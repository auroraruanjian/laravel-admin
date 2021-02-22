<?php

namespace App\Http\Controllers;

use Common\Models\UserPaymentMethods;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    //
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
        $model = UserPaymentMethods::select([
            'id',
            'user_id',
            'extra',
            'status',
            'is_delete',
            'is_open',
            'limit_amount',
            'created_at'
        ])
            ->where('user_id','=',auth()->id());


        // TODO:获取 银行卡记录等
        $banks = [

        ];

        $payment_method_list = $model->get()->toArray();
        foreach( $payment_method_list as  $key => $item ){
            $payment_method_list[$key] = array_merge($payment_method_list[$key],json_decode($item['extra'],true));
        }

        return $this->response(1,'success',[
            'banks'                 => $banks,
            'payment_method_list'   => $payment_method_list,
            'total'                 => $model->count(),
        ]);
    }

    public function postCreate(Request $request)
    {
        $account_name   = $request->get('account_name');
        $account_number = $request->get('account_number');
        $bank_id        = $request->get('bank_id');
        $branch         = $request->get('branch');
        $limit_amount   = $request->get('limit_amount');
        $province       = $request->get('province');

        $user_payment_method = new UserPaymentMethods();
        $user_payment_method->user_id       = auth()->id();
        $user_payment_method->type          = 1;
        $user_payment_method->extra         = json_encode([
            'account_name'      => $account_name,
            'account_number'    => $account_number,
            'banks_id'          => 1,  //$bank_id
            'branch'            => $branch,
            'province'          => $province
        ]);
        $user_payment_method->limit_amount  = $limit_amount;

        if( $user_payment_method->save() ){
            return $this->response(1,'success');
        }
        return $this->response(0,'error');
    }

    /**
     * 编辑收款信息
     */
    public function putEdit(Request $request)
    {

    }

    /**
     * 修改支付方式是否开启收款
     * @param Request $request
     */
    public function putIsopen(Request $request)
    {
        $id = $request->get('id');
        $is_open = $request->get('is_open') ? 1 : 0;

        $user_payment = UserPaymentMethods::select([
            'id',
            'is_open'
        ])
            ->where([
                ['user_id','=',auth()->id()],
                ['id','=',$id]
            ])
            ->first();

        if( empty($user_payment) ){
            return $this->response(0,'对不起，支付方式不存在');
        }

        $user_payment->is_open = $is_open;
        if( $user_payment->save() ){
            return $this->response(1,'状态已修改成功！');
        }

        return $this->response(0,'状态已修改失败！');
    }

    /**
     * 修改支付方式是否可用
     * @param Request $request
     */
    public function putAvailable(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status') ? 1 : 0;

        $user_payment = UserPaymentMethods::select([
            'id',
            'status'
        ])
            ->where([
                ['user_id','=',auth()->id()],
                ['id','=',$id]
            ])
            ->first();

        if( empty($user_payment) ){
            return $this->response(0,'对不起，支付方式不存在');
        }

        $user_payment->status = $status;
        if( $user_payment->save() ){
            return $this->response(1,'状态已修改成功！');
        }

        return $this->response(0,'状态已修改失败！');
    }
}
