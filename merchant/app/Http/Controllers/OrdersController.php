<?php

namespace App\Http\Controllers;

use Common\Models\Orders;
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
            'order_type.name as order_type_name'
        ])
            ->leftJoin('order_type','order_type.id','orders.order_type_id')
            ->where(function($query) use ($merchant_id){
                $query->where('orders.from_id', '=',$merchant_id)
                    ->orWhere('orders.to_id','=',$merchant_id);
            })
            ->where([
                ['orders.type','=','3']
            ]);
        $orders = $model->get();
        $total = $model->count();

        return $this->response(1,'success',[
            'orders'    => $orders,
            'total'     => $total,
        ]);
    }

    public function postIndex(Request $request)
    {

    }
}
