<?php

namespace App\Http\Controllers;

use Common\Models\Deposits;
use Common\Models\PaymentMethod;
use Common\Models\UserBanks;
use Illuminate\Http\Request;

class DepositController extends Controller
{
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
            'deposit_id'        => $request->get('deposit_id'),
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

            if(!empty( $param['deposit_id'] )){
                $deposit_id = id_decode($param['deposit_id']);
                if( !empty($deposit_id) ){
                    $query = $query->where('deposits.id','=',$deposit_id);
                }
            }

            $query = $query->where('deposits.merchant_id','=',auth()->id());
            return $query;
        };

        //
        $model = Deposits::select([
            'deposits.id',
            'deposits.merchant_order_no',
            'deposits.payment_channel_detail_id',
            'deposits.real_amount',
            'deposits.amount',
            //'deposits.merchant_fee',
            'deposits.third_fee',
            'deposits.created_at',
            'deposits.status',
            'deposits.push_status',
            'deposits.push_at',
            'charge.amount as merchant_fee',
            'payment_method.name as payment_method_name',
        ])
            ->leftJoin('payment_channel_detail','payment_channel_detail.id','deposits.payment_channel_detail_id')
            ->leftJoin('payment_method','payment_method.id','payment_channel_detail.payment_method_id')
            ->leftJoin('charge',function($join){
                $join->on('charge.deposit_withdrawal_id','deposits.id')
                    ->where([
                        ['charge.rebates_id','=','1'],
                        ['charge.type','=','2'],
                        ['charge.third_id','=',auth()->id()],
                    ]);
            })
            ->where($where);
        $deposits = $model->skip($start)
            ->take($limit)
            ->orderBy('deposits.id','desc')
            ->get()
            ->toArray();
        $total = $model->count();

        foreach($deposits as &$deposit){
            $deposit['id'] = id_encode($deposit['id']);
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

    public function postIndex(Request $request)
    {

    }
}
