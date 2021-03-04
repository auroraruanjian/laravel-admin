<?php

namespace App\Http\Controllers;

use Common\Models\UserBanks;
use Illuminate\Http\Request;

class UserPaymentMethodController extends Controller
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
            'user_payment_methods.id',
            'user_payment_methods.user_id',
            'user_payment_methods.extra',
            'user_payment_methods.status',
            'user_payment_methods.is_delete',
            'user_payment_methods.is_open',
            'user_payment_methods.limit_amount',
            'user_payment_methods.created_at',
            'users.username',
            'users.nickname',
        ])
            ->leftJoin('users','users.id','user_payment_methods.user_id')
            ->where($where);

        // TODO:获取 银行卡记录等
        $banks = [

        ];

        $payment_method_list = $model->skip($start)
            ->take($limit)
            ->get()
            ->toArray();
        foreach( $payment_method_list as  $key => $item ){
            $payment_method_list[$key] = array_merge($payment_method_list[$key],json_decode($item['extra'],true));
        }

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
