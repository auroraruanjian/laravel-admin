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
}
