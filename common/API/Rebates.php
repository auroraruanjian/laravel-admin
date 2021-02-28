<?php
namespace Common\API;

use Common\Models\AgentUsers;
use Common\Models\PaymentChannelDetail;
use Illuminate\Support\Facades\DB;

class Rebates
{
    public $error_message = '';

    /**
     * 构建代理返点数据
     * @param array $request_rebates    request中的返点数据
     * @param array $original_rebates   原始返点数据
     * @return mixed                    返回构建的返点数据，失败返回false
     */
    public function generateAgent($request_rebates,$original_rebates=null)
    {
        $merchant = $this->generateMerchant( $request_rebates , $original_rebates );
        if( !$merchant ){
            return $merchant;
        }

        $user = $this->generateUser( $request_rebates , $original_rebates );
        if( !$user ){
            return $user;
        }
        return array_merge($merchant,$user);
        /*
        $rebates = [
            'deposit_rebates'       => [],
            'withdrawal_rebate'     => [],
            'user_deposit_rebate'   => [],
            'user_withdrawal_rebate'=> [],
        ];
        if( !empty($request_rebates) ){
            // 判断返点是否合法
            // 判断返代收点是否合法
            if( !empty($request_rebates['deposit_rebates']) ){
                foreach($request_rebates['deposit_rebates'] as $rebate){
                    if( !isset($extra['rebates']) &&
                        !isset($extra['rebates'][$rebate['id']]) &&
                        $rebate['rate'] == 0 ){
                        continue;
                    }

                    $min_rate = $this->getMinRate( $rebate['id'] );
                    if( $min_rate !== false && $rebate['rate'] < $min_rate){
                        $this->error_message = $rebate['name'].'费率不能低于系统最低费率！';
                        return false;
                    }

                    // TODO：检查是否有上级，检查上级返点

                    // TODO: 检查下级


                    $rebates['deposit_rebates'][$rebate['id']] = [
                        'payment_method_id' => $rebate['id'],
                        'rate'              => $rebate['rate'],
                        'status'            => $rebate['status']
                    ];
                }
            }

            // 判断代付返点是否合法
            $withdrawal_rate = getSysConfig('rebates_withdrawal_rebate',0);

            if( !empty($request_rebates['withdrawal_rebate']) &&
                isset($request_rebates['withdrawal_rebate']['status']) && isset($request_rebates['withdrawal_rebate']['amount'])
            ){
                if( $request_rebates['withdrawal_rebate']['amount'] < $withdrawal_rate ){
                    $this->error_message = '代付返点配置错误！';
                    return false;
                }

                //TODO:获取下级返点，检查是否高于下级返点

                $rebates['withdrawal_rebate'] = [
                    'status'    => $request_rebates['withdrawal_rebate']['status'],
                    'amount'    => $request_rebates['withdrawal_rebate']['amount'],
                ];
            }

            // 判断散户代收佣金是否合法
            $user_deposit_rebate = getSysConfig('rebates_user_deposit_rebate',0);
            if( !empty($request_rebates['user_deposit_rebate']) &&
                isset($request_rebates['user_deposit_rebate']['status']) && isset($request_rebates['user_deposit_rebate']['rate'])
            ){
                if( $request_rebates['user_deposit_rebate']['rate'] > $user_deposit_rebate ){
                    $this->error_message = '散户代收佣金配置错误！';
                    return false;
                }

                //TODO:获取下级返点，检查是否高于下级返点

                $rebates['user_deposit_rebate'] = [
                    'status'    => $request_rebates['user_deposit_rebate']['status'],
                    'rate'    => $request_rebates['user_deposit_rebate']['rate'],
                ];
            }

            // 判断散户代付佣金是否合法
            $user_withdrawal_rebate = getSysConfig('rebates_user_withdrawal_rebate',0);
            if( !empty($request_rebates['user_withdrawal_rebate']) &&
                isset($request_rebates['user_withdrawal_rebate']['status']) && isset($request_rebates['user_withdrawal_rebate']['amount'])
            ){
                if( $request_rebates['user_withdrawal_rebate']['amount'] > $user_withdrawal_rebate ){
                    $this->error_message = '散户代付佣金配置错误！';
                    return false;
                }

                //TODO:获取下级返点，检查是否高于下级返点

                $rebates['user_withdrawal_rebate'] = [
                    'status'    => $request_rebates['user_withdrawal_rebate']['status'],
                    'amount'    => $request_rebates['user_withdrawal_rebate']['amount'],
                ];
            }
        }

        return $rebates;
        */
    }

    /**
     * 构建代理返点数据
     * @param array $request_rebates    request中的返点数据
     * @param array $original_rebates   原始返点数据
     * @return mixed                    返回构建的返点数据，失败返回false
     */
    public function generateMerchant($request_rebates,$original_rebates=null)
    {
        $rebates = [
            'deposit_rebates'       => [],
            'withdrawal_rebate'     => [],
        ];
        if( !empty($request_rebates) ){
            // 判断返点是否合法
            // 判断返代收点是否合法
            if( !empty($request_rebates['deposit_rebates']) ){
                foreach($request_rebates['deposit_rebates'] as $rebate){
                    if( $original_rebates!= null ){
                        if( !isset($original_rebates['rebates']) &&
                            !isset($original_rebates['rebates']['deposit_rebates'][$rebate['id']]) &&
                            $rebate['rate'] == 0 ){
                            continue;
                        }
                    }

                    $min_rate = $this->getMinRate( $rebate['id'] );
                    if( $min_rate !== false && $rebate['rate'] < $min_rate){
                        $this->error_message = $rebate['name'].'费率不能低于系统最低费率！';
                        return false;
                    }

                    // TODO：检查是否有上级，检查上级返点

                    // TODO: 检查下级


                    $rebates['deposit_rebates'][$rebate['id']] = [
                        'payment_method_id' => $rebate['id'],
                        'rate'              => $rebate['rate'],
                        'status'            => $rebate['status']
                    ];
                }
            }

            // 判断代付返点是否合法
            $withdrawal_rate = getSysConfig('rebates_withdrawal_rebate',0);

            if( !empty($request_rebates['withdrawal_rebate']) &&
                isset($request_rebates['withdrawal_rebate']['status']) && isset($request_rebates['withdrawal_rebate']['amount'])
            ){
                if( $request_rebates['withdrawal_rebate']['amount'] < $withdrawal_rate ){
                    $this->error_message = '代付返点配置错误！';
                    return false;
                }

                //TODO:获取下级返点，检查是否高于下级返点

                $rebates['withdrawal_rebate'] = [
                    'status'    => $request_rebates['withdrawal_rebate']['status'],
                    'amount'    => $request_rebates['withdrawal_rebate']['amount'],
                ];
            }
        }

        return $rebates;
    }

    /**
     * 构建代理返点数据
     * @param array $request_rebates    request中的返点数据
     * @param array $original_rebates   原始返点数据
     * @return mixed                    返回构建的返点数据，失败返回false
     */
    public function generateUser($request_rebates,$original_rebates=null)
    {
        $rebates = [
            'user_deposit_rebate'   => [],
            'user_withdrawal_rebate'=> [],
        ];
        if( !empty($request_rebates) ){
            // 判断散户代收佣金是否合法
            $user_deposit_rebate = getSysConfig('rebates_user_deposit_rebate',0);
            if( !empty($request_rebates['user_deposit_rebate']) &&
                isset($request_rebates['user_deposit_rebate']['status']) && isset($request_rebates['user_deposit_rebate']['rate'])
            ){
                if( $request_rebates['user_deposit_rebate']['rate'] > $user_deposit_rebate ){
                    $this->error_message = '散户代收佣金配置错误！';
                    return false;
                }

                //TODO:获取下级返点，检查是否高于下级返点

                $rebates['user_deposit_rebate'] = [
                    'status'    => $request_rebates['user_deposit_rebate']['status'],
                    'rate'    => $request_rebates['user_deposit_rebate']['rate'],
                ];
            }

            // 判断散户代付佣金是否合法
            $user_withdrawal_rebate = getSysConfig('rebates_user_withdrawal_rebate',0);
            if( !empty($request_rebates['user_withdrawal_rebate']) &&
                isset($request_rebates['user_withdrawal_rebate']['status']) && isset($request_rebates['user_withdrawal_rebate']['amount'])
            ){
                if( $request_rebates['user_withdrawal_rebate']['amount'] > $user_withdrawal_rebate ){
                    $this->error_message = '散户代付佣金配置错误！';
                    return false;
                }

                //TODO:获取下级返点，检查是否高于下级返点

                $rebates['user_withdrawal_rebate'] = [
                    'status'    => $request_rebates['user_withdrawal_rebate']['status'],
                    'amount'    => $request_rebates['user_withdrawal_rebate']['amount'],
                ];
            }
        }

        return $rebates;
    }


    /**
     * 获取通道最低返点
     * @param $payment_method_id
     * @return false|string
     *
     */
    public function getMinRate( $payment_method_id )
    {
        $detail_model = PaymentChannelDetail::select([
            DB::raw('max(rate) as max_rate'),
        ])
            ->where([
                //['status','=',true],
                ['payment_method_id','=',$payment_method_id]
            ])
            ->groupBy('payment_method_id')
            ->first();

        if( !empty($detail_model) ){
            $platform_min_rate = getSysConfig('rebates_deposit_platform_min_rate',0);
            return $detail_model->max_rate + $platform_min_rate;
        }else{
            return false;
        }
    }



    /*
    $rebates = [
        'deposit_rebates'       => [],
        'withdrawal_rebate'     => [],
        'user_deposit_rebate'   => [],
        'user_withdrawal_rebate'=> [],
    ];
    if( !empty($request_rebates) ){
        // 判断返代收点是否合法
        if( !empty($request_rebates['deposit_rebates']) ){
            $rebates['deposit_rebates'] = [];
            foreach($request_rebates['deposit_rebates'] as $rebate){
                if( !$rebate['status'] ) continue;
                $min_rate = $this->getMinRate( $rebate['id'] );
                if( $min_rate !== false && $rebate['rate'] >= $min_rate){
                    $rebates['deposit_rebates'][$rebate['id']] = [
                        'payment_method_id' => $rebate['id'],
                        'rate'              => $rebate['rate'],
                        'status'            => $rebate['status']
                    ];
                }
            }
        }

        // 判断代付返点是否合法
        $withdrawal_rate = getSysConfig('rebates_withdrawal_rebate',0);
        if( !empty($request_rebates['withdrawal_rebate']) &&
            isset($request_rebates['withdrawal_rebate']['status']) && isset($request_rebates['withdrawal_rebate']['amount']) &&
            $request_rebates['withdrawal_rebate']['amount'] >= $withdrawal_rate){
            $rebates['withdrawal_rebate'] = [
                'status'    => $request_rebates['withdrawal_rebate']['status'],
                'amount'    => $request_rebates['withdrawal_rebate']['amount'],
            ];
        }

        // 判断散户代收佣金是否合法
        $user_deposit_rebate = getSysConfig('rebates_user_deposit_rebate',0);
        if( !empty($request_rebates['user_deposit_rebate']) &&
            isset($request_rebates['user_deposit_rebate']['status']) && isset($request_rebates['user_deposit_rebate']['rate']) &&
            $request_rebates['user_deposit_rebate']['rate'] <= $user_deposit_rebate){
            $rebates['user_deposit_rebate'] = [
                'status'    => $request_rebates['user_deposit_rebate']['status'],
                'rate'    => $request_rebates['user_deposit_rebate']['rate'],
            ];
        }

        // 判断散户代付佣金是否合法
        $user_withdrawal_rebate = getSysConfig('rebates_user_withdrawal_rebate',0);
        if( !empty($request_rebates['user_withdrawal_rebate']) &&
            isset($request_rebates['user_withdrawal_rebate']['status']) && isset($request_rebates['user_withdrawal_rebate']['amount']) &&
            $request_rebates['user_withdrawal_rebate']['amount'] <= $user_withdrawal_rebate){
            $rebates['user_withdrawal_rebate'] = [
                'status'    => $request_rebates['user_withdrawal_rebate']['status'],
                'amount'    => $request_rebates['user_withdrawal_rebate']['amount'],
            ];
        }
    }

    return $rebates;
    */

}
