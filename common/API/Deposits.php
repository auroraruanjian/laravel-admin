<?php
namespace Common\API;


use Common\Models\AgentUsers;
use Common\Models\Charge;
use Common\Models\Merchants;
use Common\Models\Orders;
use Common\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Deposits
{
    public static $error_message = '';

    public function __construct()
    {
    }

    /**
     * 分配订单给指定散户
     */
    public static function allocation()
    {

    }

    /**
     * 订单完成
     * @param integer $id       订单id
     * @param integer $status
     * @return mixed
     */
    public static function done( $id , $status)
    {
        $deposit = \Common\Models\Deposits::select([
            'id',
            'status',
            'amount',
            'merchant_id',
            'payee_user_id',
            'payment_channel_detail_id',
        ])
            ->where('id',$id)
            ->first();

        if( empty($deposit) ){
            self::$error_message = '订单不存在';
            return false;
        }

        DB::beginTransaction();

        // 失败
        if( !$status ){
            $deposit->status = 3;
        // 成功
        }else{
            $deposit->status = 2;

            // 商户余额增加
            $order = new Orders();
            $order->type = 2;
            $order->from_id = $deposit->merchant_id;
            $order->amount = $deposit->amount;
            $order->comment = '充值订单号：'.id_encode($deposit->id);

            if( !Funds::modifyFund( $order , 'ZXCZ' ) ){
                DB::rollBack();
                self::$error_message = '充值订单生成失败5,'.Funds::$error_msg;
                Log::error(self::$error_message);
                return false;
            }


            // -------------------------------------------------------------------
            // 获取商户
            $merchant = Merchants::select(['agent_id','extra'])->where('id','=',$deposit->merchant_id)->first();
            if( empty($merchant) ){
                DB::rollBack();
                self::$error_message = '商户不存在！';
                Log::error(self::$error_message);
                return false;
            }

            $merchant_extra = json_decode($merchant->extra,true);
            if( !self::_checkRebates($merchant_extra,$deposit->payment_channel_detail_id) ){
                DB::rollBack();
                self::$error_message = '费率配置异常，请联系管理员！';
                Log::error("商户:".$deposit->merchant_id.",费率配置异常！");
                return false;
            }

            $merchant_rate = $merchant_extra['rebates']['deposit_rebates'][$deposit->payment_channel_detail_id]['rate'];
            $merchant_fee  = $deposit->amount * $merchant_rate / 100;

            if( $merchant_fee > 0 ){
                // 扣除商户手续费
                $order = new Orders();
                $order->type = 2;
                $order->from_id = $deposit->merchant_id;
                $order->amount = $merchant_fee;
                $order->comment = '充值订单号：'.id_encode($deposit->id).'，费率：'.$merchant_rate.'，手续费:'.$merchant_fee;
                //$order->ip = request()->ip();

                if( !Funds::modifyFund( $order , 'DSSXF' ,true) ){
                    DB::rollBack();
                    self::$error_message = '充值订单生成失败4,'.Funds::$error_msg;
                    Log::error(self::$error_message);
                    return false;
                }

                // 记录商户手续费
                $charge = new Charge();
                $charge->order_id = $order->id;
                $charge->rebates_id = 1;
                $charge->deposit_withdrawal_id = $deposit->id;
                $charge->type = 2;
                $charge->third_id = $deposit->merchant_id;
                $charge->rate = $merchant_rate;
                $charge->amount = $merchant_fee;
                if( !$charge->save() ){
                    DB::rollBack();
                    self::$error_message = '手续费记录失败,'.Funds::$error_msg;
                    Log::error(self::$error_message);
                    return false;
                }
            }

            // -------------------------------------------------------------------
            // 获取代理层级
            $agents = AgentUsers::select(['id','parent_id','extra'])
                ->where('parent_tree','@>',$merchant->agent_id)
                ->orWhere('id','=',$merchant->agent_id)
                ->orderBy('id','desc')
                ->get()
                ->toArray();

            $agents_data = [];
            foreach( $agents as $agent ){
                $extra = json_decode($agent['extra'],true);
                if( !self::_checkRebates($extra,$deposit->payment_channel_detail_id) ){
                    DB::rollBack();
                    self::$error_message = '费率配置异常，请联系管理员！';
                    Log::error("代理:".$agent['id'].",费率配置异常！");
                    return false;
                }

                $agents_data[] = [
                    'id'    => $agent['id'],
                    'rate'  => $extra['rebates']['deposit_rebates'][$deposit->payment_channel_detail_id]['rate']
                ];
            }

            // 计算代理费率差和费率
            foreach( $agents_data as $key => $item ){
                if( isset($agents_data[$key+1]) ){
                    $item['rate'] = $item['rate'] - $agents_data[$key+1]['rate'];
                }
                $amount = $deposit->amount * $item['rate'] / 100;

                if( $amount > 0 ){
                    $order = new Orders();
                    $order->type = 1;
                    $order->from_id = $item['id'];
                    $order->amount = $amount;
                    $order->comment = '充值订单号：'.id_encode($deposit->id).'，费率：'.$item['rate'].'，佣金:'.$amount;
                    //$order->ip = request()->ip();

                    if( !Funds::modifyFund( $order , 'DSYJ' ) ){
                        DB::rollBack();
                        self::$error_message = '充值订单生成失败1,'.Funds::$error_msg;
                        Log::error(self::$error_message);
                        return false;
                    }

                    // 记录代理佣金
                    $charge = new \Common\Models\Rebates();
                    $charge->order_id = $order->id;
                    $charge->rebates_id = 1;
                    $charge->deposit_withdrawal_id = $deposit->id;
                    $charge->type = 1;
                    $charge->third_id = $item['id'];
                    $charge->rate = $item['rate'];
                    $charge->amount = $amount;
                    if( !$charge->save() ){
                        DB::rollBack();
                        self::$error_message = '手续费记录失败,'.Funds::$error_msg;
                        Log::error(self::$error_message);
                        return false;
                    }
                }
            }
            // -------------------------------------------------------------------
        }

        if( $deposit->payee_user_id != 0 ){
            $order = new Orders();
            $order->type = 3;
            $order->from_id = $deposit->payee_user_id;
            $order->amount = $deposit->amount;
            $order->comment = '充值订单号：'.id_encode($deposit->id);
            $order->ip = request()->ip();

            $order_type_ident = 'PDDJFH';
            if( $status ){
                $order_type_ident = 'PDDJKQ';
            }
            if( !Funds::modifyFund( $order , $order_type_ident ) ){
                DB::rollBack();
                self::$error_message = '充值订单生成失败2！'.Funds::$error_msg;
                Log::error(self::$error_message);
                return false;
            }

            //---------------------------------------------------------------

            // 计算散户、散户代理 佣金
            $user = Users::select(['id','agent_id','username','extra'])->where('id','=',$deposit->payee_user_id)->first();
            /*
            $user_extra = json_decode($user['extea'],true);
            if( !self::_checkRebates($user_extra,$deposit->payment_channel_detail_id) ){
                DB::rollBack();
                self::$error_message = '费率配置异常，请联系管理员！';
                Log::error("用户:".$deposit->payee_user_id.",费率配置异常！");
                return false;
            }
            $user_rate = $user_extra['rebates']['deposit_rebates'][$deposit->payment_channel_detail_id]['rate'];
            $user_fee  = $deposit->amount * $user_rate;

            if( $user_fee > 0 ){
                // 派发散户佣金
                $order = new Orders();
                $order->type = 3;
                $order->from_id = $user->id;
                $order->amount = $user_fee;
                $order->comment = '充值订单号：'.id_encode($deposit->id).'，费率：'.$user_rate.'，手续费:'.$user_fee;
                //$order->ip = request()->ip();

                if( !Funds::modifyFund( $order , 'DSYJ' ) ){
                    DB::rollBack();
                    self::$error_message = '充值订单生成失败,'.Funds::$error_msg;
                    Log::error(self::$error_message);
                    return false;
                }
            }
            */

            // -------------------------------------------------------------------
            // 获取代理层级
            $user_agents = AgentUsers::select(['id','parent_id','extra'])
                ->where('parent_tree','@>',$user->agent_id)
                ->orWhere('id','=',$user->agent_id)
                ->orderBy('id','asc')
                ->get()
                ->toArray();

            $users_agent_data = [];
            foreach( $user_agents as $user_agent ){
                $extra = json_decode($user_agent['extra'],true);
                if( !self::_checkRebates($extra,$deposit->payment_channel_detail_id) ){
                    DB::rollBack();
                    self::$error_message = '费率配置异常，请联系管理员！';
                    Log::error("代理:".$user_agent['id'].",费率配置异常！");
                    return false;
                }

                $users_agent_data[] = [
                    'id'    => $user_agent['id'],
                    'rate'  => $extra['rebates']['deposit_rebates'][$deposit->payment_channel_detail_id]['rate'],
                    'type'  => 'agent',
                ];
            }

            $user_extra = json_decode($user->extra,true);
            if( !isset($user_extra['rebates']) ||
                !isset($user_extra['rebates']['user_deposit_rebate']) ||
                !isset($user_extra['rebates']['user_deposit_rebate']['rate'])
            ){
                DB::rollBack();
                self::$error_message = '费率配置异常，请联系管理员！';
                Log::error("散户:".$user->id.",费率配置异常！");
                return false;
            }
            array_push($users_agent_data,[
                'id'    => $user->id,
                'rate'  => $user_extra['rebates']['user_deposit_rebate']['rate'],
                'type'  => 'user',
            ]);

            // 计算代理费率差和费率
            foreach( $users_agent_data as $key => $item ){
                if( isset($users_agent_data[$key+1]) ){
                    $item['rate'] = $item['rate'] - $users_agent_data[$key+1]['rate'];
                }
                $amount = $deposit->amount * $item['rate'] / 100;

                if( $amount > 0 ){
                    $order = new Orders();
                    $order->type = $item['type']=='agent'?1:3;
                    $order->from_id = $item['id'];
                    $order->amount = $amount;
                    $order->comment = '充值订单号：'.id_encode($deposit->id).'，费率：'.$item['rate'].'，佣金:'.$amount;
                    //$order->ip = request()->ip();

                    if( !Funds::modifyFund( $order , 'DSYJ' ) ){
                        DB::rollBack();
                        self::$error_message = '充值订单生成失败3,'.Funds::$error_msg;
                        Log::error(self::$error_message);
                        return false;
                    }

                    // 记录代理佣金
                    $charge = new \Common\Models\Rebates();
                    $charge->order_id = $order->id;
                    $charge->rebates_id = 1;
                    $charge->deposit_withdrawal_id = $deposit->id;
                    $charge->type = $item['type']=='agent'?1:3;
                    $charge->third_id = $item['id'];
                    $charge->rate = $item['rate'];
                    $charge->amount = $amount;
                    if( !$charge->save() ){
                        DB::rollBack();
                        self::$error_message = '手续费记录失败,'.Funds::$error_msg;
                        Log::error(self::$error_message);
                        return false;
                    }
                }
            }
            // -------------------------------------------------------------------
        }
        // 如果为散户接单，则 返还或扣除 散户冻结金额

        // 修改订单状态
        if( $deposit->save() ){
            DB::commit();
            return true;
        }

        DB::rollBack();
        self::$error_message = '状态修改失败';
        return false;
    }


    /**
     * @param $extra
     * @return bool
     */
    public static function _checkRebates( $extra , $channel_detail_id )
    {
        if( !isset($extra['rebates']) ||
            !isset($extra['rebates']['deposit_rebates']) ||
            !isset($extra['rebates']['deposit_rebates'][$channel_detail_id]) ||
            !isset($extra['rebates']['deposit_rebates'][$channel_detail_id]['rate'])
        ){
            return false;
        }

        return true;
    }
}
