<?php

namespace App\Http\Controllers;

use Common\Models\Deposits;
use Common\Models\UserWithdrawals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
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
        //
        $withdrawal_model = UserWithdrawals::select([
            'id',
            'amount',
            'user_id',
            'merchant_fee',
            'third_fee',
            'status',
            'extra',
            'created_at'
        ])
            ->where([
                ['user_id','=',auth()->id()],
            ]);

        $withdrawals = $withdrawal_model->get()->toArray();
        $count = $withdrawal_model->count();

        foreach( $withdrawals as &$withdrawal){
            $withdrawal['extra'] = json_decode($withdrawal['extra'],true);
        }

        return $this->response(1,'success',[
            'withdrawals'   => $withdrawals,
            'total'         => $count
        ]);
    }

    public function postApply(Request $request)
    {
        $amount         = $request->get('amount');
        $bank_id        = $request->get('bank_id');
        $account_number = $request->get('account_number');
        $account_name   = $request->get('account_name');
        $province       = $request->get('province');
        $branch         = $request->get('branch');
        $pay_password   = $request->get('pay_password');

        // TODO 余额判断

        // TODO 支付密码判断

        // TODO 手续费计算

        $withdralwal = new UserWithdrawals();
        $withdralwal->amount        = $amount;
        $withdralwal->user_id       = auth()->id();
        $withdralwal->merchant_fee  = 0;    // TODO 计算用户手续费
        $withdralwal->third_fee     = 0;    // TODO 计算用户手续费
        $withdralwal->extra         = json_encode([
            'bank_id'           => $bank_id,
            'account_number'    => $account_number,
            'account_name'      => $account_name,
            'province'          => $province,
            'branch'            => $branch,
        ]);

        if( $withdralwal->save() ){
            return $this->response(1,'提现申请提交成功！');
        }

        return $this->response(0,'提现申请提交失败！');
    }
}
