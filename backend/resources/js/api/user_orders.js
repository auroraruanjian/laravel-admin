import fetch from '@/utils/fetch'

export function getOrders( data ) {
    return fetch({
        url: 'user_orders',
        method: 'get',
        params:data
    });
}
