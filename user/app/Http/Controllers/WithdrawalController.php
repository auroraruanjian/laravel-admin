<?php

namespace App\Http\Controllers;

use Common\Models\Withdrawals;
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
        // TODO:获取 银行卡记录等
        $model = Withdrawals::select([
            'id',
            'extra',
            'amount',
            'merchant_fee',
            'status',
            'payee_user_id',
            'created_at',
            'done_at'
        ])
            ->where([
                ['payee_user_id','=',auth()->id()],
            ]);
        $withdrawals = $model->get();
        $total = $model->count();

        foreach( $withdrawals as &$withdrawal ){
            $withdrawal['extra'] = json_decode($withdrawal['extra'],true);
        }

        return $this->response(1,'success',[
            'withdrawals'=> $withdrawals,
            'total'      => $total,
        ]);
    }

    public function postChanagestatus(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');

        $withdrawal = Withdrawals::select([
            'id',
            'status',
            'amount',
        ])
            ->where('id',$id)
            ->first();

        if( empty($withdrawal) ){
            return $this->response(0,'订单不存在');
        }

        DB::beginTransaction();

        // 失败
        if( $status != true){
            $withdrawal->status = 3;
        // 成功
        }else{
            $withdrawal->status = 2;
            // TODO:扣减散户保证金

            // TODO:计算代理、散户返点

            // TODO:增加返点
        }

        // 修改订单状态
        if( $withdrawal->save() ){


            DB::commit();
            return $this->response(1,'状态修改成功！');
        }

        DB::rollBack();
        return $this->response(0,'状态修改失败！');

    }
}
