import fetch from '@/utils/fetch'

export function getOrders( data ) {
    return fetch({
        url: 'merchant_orders',
        method: 'get',
        params:data
    });
}
