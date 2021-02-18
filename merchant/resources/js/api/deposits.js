import fetch from '@/utils/fetch'

export function getAllRecord( data ) {
    return fetch({
        url: 'deposit',
        method: 'get',
        params:data
    });
}
