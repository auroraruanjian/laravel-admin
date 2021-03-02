import fetch from '@/utils/fetch'

export function getAllMethod( data ) {
    return fetch({
        url: 'user_payment_method',
        method: 'get',
        params:data
    });
}

export function putIsOpen( data ){
    return fetch({
        url: 'payment_method/isopen',
        method: 'put',
        data
    });
}

export function putAvailable( data ){
    return fetch({
        url: 'payment_method/available',
        method: 'put',
        data
    });
}
