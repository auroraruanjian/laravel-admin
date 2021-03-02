import fetch from '@/utils/fetch'

export function getOrders( data ) {
    return fetch({
        url: 'agent_orders',
        method: 'get',
        params:data
    });
}
