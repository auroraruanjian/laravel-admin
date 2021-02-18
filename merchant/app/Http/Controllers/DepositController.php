<?php

namespace App\Http\Controllers;

use Common\Models\Deposits;
use Illuminate\Http\Request;

class DepositController extends Controller
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
        // TODO:获取 银行卡记录等
        $model = Deposits::select([
            'id',
            'merchant_order_no',
            'payment_channel_detail_id',
            'real_amount',
            'amount',
            'merchant_fee',
            'third_fee',
            'created_at',
            'status',
            'push_status',
            'push_at',
        ])
            ->where([
                ['payee_user_id','=',auth()->id()],
            ]);
        $deposit = $model->get();
        $total = $model->count();

        return $this->response(1,'success',[
            'deposits'  => $deposit,
            'total'     => $total,
        ]);
    }

    public function postIndex(Request $request)
    {

    }
}
