<?php

namespace App\Http\Controllers;


use Common\Models\Deposits;
use Common\API\Deposits as API_Deposits;
use Common\Models\PaymentMethod;
use Common\Models\UserBanks;
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
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $param = [
            'time'              => $request->get('time'),
            'status'            => $request->get('status'),
            'payment_method_id' => $request->get('payment_method_id'),
            'form_type'         => $request->get('form_type'),
            'id_or_account_number' => $request->get('id_or_account_number'),
        ];
        $where = function( $query ) use ($param){
            if( !empty( $param['time'] ) ){
                $query = $query->whereBetween('deposits.created_at',[$param['time'][0],$param['time'][1]]);
            }

            if( !empty( $param['status'] ) ){
                if( $param['status'] == 1 || $param['status'] == 5){
                    $status = [3,5];
                    if( $param['status'] == 1 ){
                        $status = [0,1];
                    }
                    $query = $query->whereIn('deposits.status',$status);
                }else{
                    $query = $query->where('deposits.status','=',$param['status']);
                }
            }

            if( !empty( $param['payment_method_id'] ) ){
                $query = $query->where('payment_method.id','=',$param['payment_method_id']);
            }

            if(!empty( $param['form_type'] ) && !empty($param['id_or_account_number'])){
                if( $param['form_type'] == 1 ){
                    $deposit_id = id_decode($param['id_or_account_number']);
                    if( !empty($deposit_id) ){
                        $query = $query->where('deposits.id','=',$deposit_id);
                    }
                }else{
                    $user_payment_method = UserBanks::whereRaw("extra->>'account_number' = '".$param['id_or_account_number']."'")->first('id');
                    if( !empty($user_payment_method) ){
                        $query = $query->where('deposits.account_number','=',$user_payment_method->id);
                    }else{
                        $query = $query->whereRaw("false");
                    }
                }
            }

            $query = $query->where('deposits.payee_user_id','=',auth()->id());
            return $query;
        };

        // TODO:获取 银行卡记录等
        $model = Deposits::select([
            'deposits.id',
            'deposits.amount',
            'deposits.real_amount',
            'deposits.status',
            'deposits.payee_user_id',
            'deposits.created_at',
            'deposits.done_at',
            'deposits.remark',
            'deposits.account_number',
            'payment_method.name as payment_method_name',
            'payment_channel_detail.payment_channel_id',
            'rebates.amount as rebates_amount',
        ])
            ->leftJoin('payment_channel_detail','payment_channel_detail.id','deposits.payment_channel_detail_id')
            ->leftJoin('payment_method','payment_method.id','payment_channel_detail.payment_method_id')
            ->leftJoin('rebates',function ($join){
                $join->on('rebates.deposit_withdrawal_id','=','deposits.id')
                    ->where('rebates.rebates_id','=',1)
                    ->where('rebates.type','=',3);
            })
            ->where($where);

        $deposits = $model->skip($start)
            ->take($limit)
            ->orderBy('deposits.id','desc')
            ->get()
            ->toArray();

        $total = $model->count();

        foreach( $deposits as &$deposit ){
            $deposit['payee_account_name'] = '';
            $deposit['payee_account_number'] = '';

            if( isset($deposit['payment_channel_id']) && $deposit['payment_channel_id'] == 1){
                $user_payment_method = UserBanks::select([
                    'user_payment_methods.account_name',
                    'user_payment_methods.account_number',
                ])
                    ->where('user_payment_methods.account_number','=',$deposit['account_number'])
                    ->first();

                if( $user_payment_method->type == 1 ){
                    $extra = json_decode($user_payment_method->extra,true);

                    $deposit['payee_account_name'] = $extra['account_name']??'';
                    $deposit['payee_account_number'] = $extra['account_number']??'';
                }
            }
        }

        $payment_method = PaymentMethod::select([
            'id',
            'ident',
            'name',
        ])
            ->where('status','=',true)
            ->get()
            ->toArray();

        return $this->response(1,'success',[
            'deposits'      => $deposits,
            'total'         => $total,
            'payment_method'=>$payment_method
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
