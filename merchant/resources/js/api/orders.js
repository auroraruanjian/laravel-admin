import fetch from '@/utils/fetch'

export function getAllRecord( data ) {
    return fetch({
        url: 'orders',
        method: 'get',
        params:data
    });
}
