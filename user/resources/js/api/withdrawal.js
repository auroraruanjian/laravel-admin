import fetch from '@/utils/fetch'

export function getAllRecord( data ) {
    return fetch({
        url: 'withdrawal',
        method: 'get',
        params:data
    });
}

export function postChanagestatus( data ) {
    return fetch({
        url: 'withdrawal/chanagestatus',
        method: 'post',
        data:data
    });
}
