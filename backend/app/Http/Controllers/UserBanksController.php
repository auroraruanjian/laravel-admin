<?php

namespace App\Http\Controllers;

use Common\Models\UserBanks;
use Illuminate\Http\Request;

class UserBanksController extends Controller
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
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $param = [];
        $param['username'] = $request->get('username');
        $param['payment_method_id'] = $request->get('payment_method_id');

        $where = function( $query ) use ($param){
            if( !empty($param['username']) ){
                $query = $query->where('users.username','=',$param['username']);
            }
            if( !empty($param['payment_method_id']) ){
                $query = $query->where('user_payment_methods.type','=',$param['payment_method_id']);
            }
            return $query->whereRaw(' true ');
        };

        $model = UserBanks::select([
            'user_banks.id',
            'user_banks.user_id',
            'user_banks.account_name',
            'user_banks.account_name',
            'user_banks.account_number',
            'user_banks.branch',
            'user_banks.status',
            'user_banks.is_delete',
            'user_banks.is_open',
            'user_banks.limit_amount',
            'user_banks.created_at',
            'users.username',
            'users.nickname',
            'banks.name as bank_name',
            'r1.name as province',
            'r2.name as city',
            'r3.name as district',
        ])
            ->leftJoin('users','users.id','user_banks.user_id')
            ->leftJoin('banks','banks.id','user_banks.banks_id')
            ->leftJoin('regions as r1','r1.id','user_banks.province_id')
            ->leftJoin('regions as r2','r2.id','user_banks.city_id')
            ->leftJoin('regions as r3','r3.id','user_banks.district_id')
            ->where($where);

        // TODO:获取 银行卡记录等
        $banks = [

        ];

        $payment_method_list = $model->skip($start)
            ->take($limit)
            ->get()
            ->toArray();

        return $this->response(1,'success',[
            'banks'                 => $banks,
            'payment_method_list'   => $payment_method_list,
            'total'                 => $model->count(),
        ]);
    }

    /**
     * 修改支付方式是否开启收款
     * @param Request $request
     */
    public function putIsopen(Request $request)
    {
        $id = $request->get('id');
        $is_open = $request->get('is_open') ? 1 : 0;

        $user_payment = UserBanks::select([
            'id',
            'is_open'
        ])
            ->where([
                ['user_id','=',auth()->id()],
                ['id','=',$id]
            ])
            ->first();

        if( empty($user_payment) ){
            return $this->response(0,'对不起，支付方式不存在');
        }

        $user_payment->is_open = $is_open;
        if( $user_payment->save() ){
            return $this->response(1,'状态已修改成功！');
        }

        return $this->response(0,'状态已修改失败！');
    }

    /**
     * 修改支付方式是否可用
     * @param Request $request
     */
    public function putAvailable(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status') ? 1 : 0;

        $user_payment = UserBanks::select([
            'id',
            'status'
        ])
            ->where([
                ['user_id','=',auth()->id()],
                ['id','=',$id]
            ])
            ->first();

        if( empty($user_payment) ){
            return $this->response(0,'对不起，支付方式不存在');
        }

        $user_payment->status = $status;
        if( $user_payment->save() ){
            return $this->response(1,'状态已修改成功！');
        }

        return $this->response(0,'状态已修改失败！');
    }
}
