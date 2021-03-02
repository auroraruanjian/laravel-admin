<?php

namespace App\Http\Controllers;

use Common\API\Orders;
use Common\Models\OrderType;
use Illuminate\Http\Request;

class MerchantOrdersController extends Controller
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

        $param['type']          = 2;
        $data = Orders::getData($param,$start,$limit);

        $order_type = OrderType::select([
            'id',
            'ident',
            'name'
        ])
            ->whereIn('category',[1,3])
            ->get()
            ->toArray();

        return $this->response(1,'success',[
            'orders'    => $data['orders'],
            'total'     => $data['total'],
            'order_type'=> $order_type
        ]);
    }
}
