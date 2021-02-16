import fetch from '@/utils/fetch'

export function getAllRecord( data ) {
    return fetch({
        url: 'deposit',
        method: 'get',
        params:data
    });
}

export function postChanagestatus( data ) {
    return fetch({
        url: 'deposit/chanagestatus',
        method: 'post',
        data:data
    });
}
