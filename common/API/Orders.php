<?php
namespace Common\API;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Common\Models\Orders as OrdersModel;

class Orders
{
    public static function getData( $param , $start , $limit )
    {
        $where = function( $query ) use( $param ) {
            if( !empty($param['time']) ){
                $query = $query->whereBetween('orders.created_at',[$param['time'][0],$param['time'][1]]);
            }
            if( !empty($param['order_type_id']) ){
                $query = $query->where('orders.order_type_id','=',$param['order_type_id']);
            }

            if( !empty($param['third_id']) ){
                $query = $query->where(function($query) use ( $param ){
                    $query->where('orders.from_id', '=',$param['third_id'])
                        ->orWhere('orders.to_id','=',$param['third_id']);
                });
            }

            if( !empty($param['type']) ){
                $query = $query->where('orders.type','=',$param['type']);
            }

            return $query->whereRaw(' true ');
        };

        // TODO:获取 银行卡记录等
        $model = OrdersModel::select([
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
            ->where($where);

        $orders = $model->skip($start)
            ->take($limit)
            ->orderBy('id','desc')
            ->get()
            ->toArray();

        foreach( $orders as &$order ){
            $order['id'] = id_encode($order['id']);
        }

        $total = $model->count();

        return [
            'total'     => $total,
            'orders'    => $orders
        ];
    }
}
