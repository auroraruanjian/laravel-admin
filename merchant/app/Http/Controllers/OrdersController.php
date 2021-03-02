<?php

namespace App\Http\Controllers;

use Common\API\Orders;
use Common\Models\OrderType;
use Illuminate\Http\Request;

class OrdersController extends Controller
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

        $param = [];
        $param['order_type_id'] = $request->get('order_type_id');
        $param['time']          = $request->get('time');
        $param['payment_method_id']= $request->get('payment_method_id');

        // 用户类型
        $param['type'] = 2;
        $param['third_id'] = auth()->id();

        $data = Orders::getData($param,$start,$limit);

        $order_type = OrderType::select([
            'id',
            'ident',
            'name'
        ])
            ->whereIn('category',[1,4])
            ->get()
            ->toArray();

        return $this->response(1,'success',[
            'orders'    => $data['orders'],
            'total'     => $data['total'],
            'order_type'=> $order_type
        ]);

        /*
        $where = function( $query ) use( $param ) {
            if( !empty($param['time']) ){
                $query = $query->whereBetween('orders.created_at',[$param['time'][0],$param['time'][1]]);
            }
            if( !empty($param['order_type_id']) ){
                $query = $query->where('orders.order_type_id','=',$param['order_type_id']);
            }

            return $query->where('orders.type','=','2');
        };

        $merchant_id = auth()->id();

        // TODO:获取 银行卡记录等
        $model = Orders::select([
            'orders.id',
            'orders.type',
            'orders.from_id',
            'orders.to_id',
            'orders.order_type_id',
            'orders.amount',
            'orders.pre_balance',
            'orders.pre_hold_balance',
            'orders.balance',
            'orders.hold_balance',
            'orders.ip',
            'orders.comment',
            'orders.created_at',
            'order_type.name as order_type_name',
            'order_type.operation',
            'order_type.hold_operation',
        ])
            ->leftJoin('order_type','order_type.id','orders.order_type_id')
            ->where(function($query) use ($merchant_id){
                $query->where('orders.from_id', '=',$merchant_id)
                    ->orWhere('orders.to_id','=',$merchant_id);
            })
            ->where($where);
        $orders = $model->skip($start)
            ->take($limit)
            ->orderBy('id','desc')
            ->get()
            ->toArray();
        $total = $model->count();

        $order_type = OrderType::select([
            'id',
            'ident',
            'name'
        ])
            ->whereIn('category',[1,4])
            ->get()
            ->toArray();

        return $this->response(1,'success',[
            'orders'    => $orders,
            'total'     => $total,
            'order_type'=> $order_type
        ]);
        */
    }

    public function postIndex(Request $request)
    {

    }
}
