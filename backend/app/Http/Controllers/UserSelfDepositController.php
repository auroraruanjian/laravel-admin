<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Common\API\Funds;
use Common\Models\Deposits;
use Common\Models\Orders;
use Common\Models\PaymentMethod;
use Common\Models\UserDeposits;
use Illuminate\Http\Request;
use DB;

class UserSelfDepositController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getIndex(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $param = [];
        $param['time']              = $request->get('time');
        $param['deposit_id']        = $request->get('deposit_id');

        $where = function ($query) use($param){
            if( !empty($param['time']) ){
                $query = $query->whereBetween('orders.created_at',[$param['time'][0],$param['time'][1]]);
            }
            if( !empty($param['deposit_id']) ){
                $param['deposit_id'] = id_decode($param['deposit_id']);
                if( !empty($param['deposit_id']) ){
                    $query = $query->where('user_deposits.id','=',$param['deposit_id']);
                }else{
                    $query = $query->whereRaw(" false ");
                }
            }
            return $query->whereRaw(" true ");
        };

        $deposits_model = \Common\Models\UserDeposits::select([
            'user_deposits.id',
            'users.username',
            'user_deposits.amount',
            'user_deposits.real_amount',
            'user_deposits.manual_amount',
            'user_deposits.order_id',
            'user_deposits.accountant_admin_id',
            'user_deposits.cash_admin_id',
            'au1.nickname as accountant_admin_name',
            'au2.nickname as cash_admin_name',
            'user_deposits.status',
            'user_deposits.done_at',
            'user_deposits.created_at'
        ])
            ->leftJoin('users','users.id','user_deposits.user_id')
            ->leftJoin('admin_users as au1','au1.id','user_deposits.accountant_admin_id')
            ->leftJoin('admin_users as au2','au2.id','user_deposits.cash_admin_id')
            ->orderBy('id', 'asc')
            ->where($where);

        $deposits = $deposits_model->skip($start)
            ->take($limit)
            ->get()
            ->toArray();

        $total = $deposits_model->count();

        foreach( $deposits as $key => &$val ){
            $val['id'] = id_encode($val['id']);
        }

        $payment_method = PaymentMethod::select([
            'id',
            'ident',
            'name',
        ])
            ->where('status','=',true)
            ->get()
            ->toArray();

        return $this->response(1, 'Success!', [
            'total'       => $total,
            'deposits'    => $deposits,
            'payment_method' => $payment_method
        ]);
    }

    /**
     * 人工审核
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(Request $request)
    {
        $id = $request->get('id');

        $deposit = UserDeposits::select([
            'user_deposits.account_number',
            'user_deposits.user_id',
            'users.username',
            'user_deposits.created_at',
            'user_deposits.ip',
            'user_deposits.merchant_fee',
            'user_deposits.third_fee',
            'user_deposits.amount',
            'user_deposits.real_amount',
            'user_deposits.remark',
            'user_deposits.manual_amount',
            'user_deposits.merchant_fee',
            'user_deposits.manual_postscript',
            'user_deposits.third_order_no',
            'user_deposits.accountant_admin_id',
            'user_deposits.deal_at',
        ])
            ->leftJoin('users','users.id','user_deposits.user_id')
            ->where('user_deposits.id','=',id_decode($id))
            ->first();

        if (empty($deposit)) {
            return $this->response(0, '记录不存在');
        }

        $deposit = $deposit->toArray();
        $deposit['id'] = $id;

        return $this->response(1, 'success', $deposit);
    }

    public function putDeal(Request $request)
    {
        $id = $request->get('id');

        // 获取订单，检查订单状态
        $deposit = UserDeposits::where('id',id_decode($id))->first();
        if( $deposit->status == 0 ){
            $deposit->manual_amount = $request->get('manual_amount');
            $deposit->merchant_fee = $request->get('merchant_fee');
            $deposit->manual_postscript = $request->get('manual_postscript');
            $deposit->third_order_no = $request->get('third_order_no');
            $deposit->status = 1;
            $deposit->accountant_admin_id = auth()->user()->id;
            $deposit->deal_at = (string)Carbon::now();

            if($deposit->save()){
                return $this->response(1, '操作成功');
            }
        }

        return $this->response(0, '操作失败');
    }

    public function putVerify(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');  // 限制 2 3

        // 获取订单，检查订单状态
        $deposit = UserDeposits::where('id',id_decode($id))->first();
        if( $deposit->status == 1 ){
            if( $status === '2' ){
                DB::beginTransaction();
                // 增加账变
                $order = new Orders();
                $order->type = 3;
                $order->from_id = $deposit->user_id;
                $order->amount = $deposit->amount;
                $order->admin_user_id = auth()->id();
                $order->comment = '充值订单号：'.id_encode($deposit->id);
                $order->ip = request()->ip();

                if( Funds::modifyFund( $order , 'ZXCZ' ) ){
                    $deposit->status = $status;
                    $deposit->cash_admin_id = auth()->user()->id;
                    $deposit->done_at = (string)Carbon::now();
                    $deposit->order_id = $order->id;

                    if($deposit->save()){
                        DB::commit();
                        return $this->response(1, '操作成功');
                    }
                }
                DB::rollBack();
                return $this->response('充值订单生成失败2！'.Funds::$error_msg);
            }elseif( $status === '3' ){
                $deposit->status = $status;
                $deposit->cash_admin = auth()->user()->username;

                if($deposit->save()){
                    return $this->response(1, '操作成功');
                }
            }
        }

        return $this->response(0, '操作失败');
    }
}
