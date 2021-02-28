<?php

namespace App\Http\Controllers;


use Common\Models\Deposits;
use Common\API\Deposits as API_Deposits;
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
        $model = Deposits::select([
            'id',
            'amount',
            'status',
            'payee_user_id',
            'created_at'
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

    public function postChanagestatus(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');

        if( API_Deposits::done( $id , $status ) ){
            return $this->response(1,'状态修改成功！');
        }else{
            return $this->response(0,API_Deposits::$error_message);
        }
    }
}
