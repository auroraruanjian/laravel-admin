<?php

namespace App\Http\Controllers;

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
        // TODO
        // 获取支付通道，限额等 信息
    }

    public function postApply( Request $request)
    {
        $amount = $request->get('amount');

        // 检查支付通道等信息

        // 写入充值记录

        //

        return $this->response(1,'申请提交成功！');
    }
}
