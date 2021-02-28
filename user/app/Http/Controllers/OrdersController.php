<?php

namespace App\Http\Controllers;

use Common\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
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

        $order_type_id = $request->get('order_type_id');
        $user = auth()->user();

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
            ->where(function($query) use ($user){
                $query->where('orders.from_id', '=',$user->id)
                    ->orWhere('orders.to_id','=',$user->id);
            })
            ->where([
                ['orders.type','=','3']
            ]);

        $orders = $model
            ->skip($start)
            ->take($limit)
            ->orderBy('id','desc')
            ->get()
            ->toArray();

        foreach( $orders as &$order ){
            $order['id'] = id_encode($order['id']);
        }

        $total = $model->count();

        return $this->response(1,'success',[
            'orders'    => $orders,
            'total'     => $total,
        ]);
    }

}
