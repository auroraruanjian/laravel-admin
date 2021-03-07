<?php

namespace App\Http\Controllers;

use Common\Models\Bank;
use Common\Models\Deposits;
use Common\Models\Funds;
use Common\Models\UserBanks;
use Common\Models\UserWithdrawals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserWithdrawalController extends Controller
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

        $user_withdrawals = $withdrawal_model->orderBy('id','desc')->get()->toArray();
        $count = $withdrawal_model->count();

        foreach( $user_withdrawals as &$withdrawal){
            $withdrawal['extra'] = json_decode($withdrawal['extra'],true);
            if( !empty($withdrawal['extra']['bank_id']) ){
                $withdrawal['bank_name'] = Bank::where('id','=',$withdrawal['extra']['bank_id'])->value('name');
            }
        }

        return $this->response(1,'success',[
            'user_withdrawals'  => $user_withdrawals,
            'total'             => $count,
            'hasSecurityPwd'    => auth()->user()->security_password?true:false,
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

        // 余额判断
        $balance = Funds::where([['type','=','3'],['third_id','=',auth()->id()]])->first('balance');
        if( $amount > $balance ){
            return $this->response(0,'对不起，余额不足！');
        }

        // 支付密码判断
        $user = auth()->user();
        $security_password  = $request->get('security_password');
        if (!Hash::check($security_password, $user->security_password)) {
            return $this->response(0,'资金密码不正确');
        }

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
