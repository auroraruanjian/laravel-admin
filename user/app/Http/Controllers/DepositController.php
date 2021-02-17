<?php

namespace App\Http\Controllers;

use Common\Models\Deposits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
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
        $deposit = Deposits::select([
            'id',
            'amount',
            'status',
            'payee_user_id',
            'created_at'
        ])
            ->where([
                ['payee_user_id','=',auth()->id()],
            ])
            ->get();

        return $this->response(1,'success',[
            'deposits'  => $deposit
        ]);
    }

    public function postChanagestatus(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');

        $deposit = Deposits::select([
            'id',
            'status',
            'amount',
        ])
            ->where('id',$id)
            ->first();

        if( empty($deposit) ){
            return $this->response(0,'订单不存在');
        }

        DB::beginTransaction();

        // 失败
        if( $status != true){
            $deposit->status = 3;
        // 成功
        }else{
            $deposit->status = 2;
            // TODO:扣减散户保证金

            // TODO:计算代理、散户返点

            // TODO:增加返点
        }

        // 修改订单状态
        if( $deposit->save() ){


            DB::commit();
            return $this->response(1,'状态修改成功！');
        }

        DB::rollBack();
        return $this->response(0,'状态修改失败！');

    }
}
