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

    /**
     *
     */
    public function getIndex(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit');

        $start = ($page - 1) * $limit;

        $param = [];
        $param['order_type_id'] = $request->get('order_type_id');
        $param['time']          = $request->get('time');

        $data = Orders::getData($param,$start,$limit);

        $order_type = OrderType::select([
            'id',
            'ident',
            'name'
        ])
            ->get()
            ->toArray();

        return $this->response(1,'success',[
            'orders'    => $data['orders'],
            'total'     => $data['total'],
            'order_type'=> $order_type
        ]);
        /*
        $data = [
            'total'       => 0,
            'orders'      => [],
        ];

        $orders = Orders::select([
            '*'
        ])
            ->orderBy('id', 'asc')
            ->skip($start)
            ->take($limit)
            ->get();

        $data['total'] = Orders::count();

        if (!$orders->isEmpty()) {
            $data['orders'] = $orders->toArray();
        }

        return $this->response(1, 'Success!', $data);
        */
    }
}
