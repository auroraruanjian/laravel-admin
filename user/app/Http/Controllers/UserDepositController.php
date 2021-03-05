<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Common\Models\UserBanks;
use Common\Models\UserDeposits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserDepositController extends Controller
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
        // TODO:获取 银行卡记录等
        $model = UserDeposits::select([
            'id',
            'amount',
            'status',
            'created_at'
        ])
            ->where([
                ['user_id','=',auth()->id()],
            ]);
        $user_deposit = $model->get();
        $total = $model->count();

        return $this->response(1,'success',[
            'user_deposits' => $user_deposit,
            'total'         => $total,
        ]);
    }

    public function getApply( Request $request)
    {
        $data = UserBanks::select([
            'user_banks.id',
            'user_banks.user_id',
            'user_banks.account_name',
            'user_banks.account_number',
            'banks.name as bank_name',
            'user_banks.branch',
        ])
            ->leftJoin('banks','banks.id','user_banks.banks_id')
            ->where([
                ['user_banks.user_id','=',0],
                ['user_banks.is_delete','=',1]
            ])
            ->first();

        if( empty($data) ){
            return $this->response(0,'充值方式不存在！');
        }

        return $this->response(1,'success',[
            'bank_info' => $data
        ]);
    }

    public function postApply( Request $request)
    {
        $id = $request->get('bank_id');
        $amount = $request->get('amount');

        $bank = UserBanks::select(['account_number'])
            ->where([
                ['id','=',$id],
                ['user_id','=',0]
            ])
            ->first();

        // 检查支付通道等信息
        if( empty($bank) ){
            return $this->response(0,'对不起，收款卡不存在！');
        }

        // 写入充值记录
        $deposits_model = new UserDeposits();
        $deposits_model->user_id = auth()->id();
        $deposits_model->amount = $amount;
        $deposits_model->account_number = $bank->account_number;
        $deposits_model->ip = $request->ip();
        $deposits_model->created_at = Carbon::now();
        if( $deposits_model->save() ){
            return $this->response(1,'申请提交成功！');
        }
        return $this->response(0,'申请提交失败！');
    }
}
