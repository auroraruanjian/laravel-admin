<?php

namespace App\Http\Controllers;

use Common\Models\Withdrawals;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
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
        $model = Withdrawals::select([
            'id',
            'merchant_order_no',
            'extra',
            'amount',
            'merchant_fee',
            'status',
            'remark',
            'done_at',
            'merchant_id',
            'cash_admin_id',
            'status',
        ])
            ->where([
                ['payee_user_id','=',auth()->id()],
            ]);
        $withdrawals = $model->get();
        $total = $model->count();


//            'account_number',
//            'account_name',
//            'bank_code',
//            'branch',
        //    'transfer_account','

        return $this->response(1,'success',[
            'withdrawals'   => $withdrawals,
            'total'         => $total,
        ]);
    }

    public function postIndex(Request $request)
    {

    }
}
