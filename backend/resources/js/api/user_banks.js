import fetch from '@/utils/fetch'

export function getAllMethod( data ) {
    return fetch({
        url: 'user_banks',
        method: 'get',
        params:data
    });
}

export function putIsOpen( data ){
    return fetch({
        url: 'user_banks/isopen',
        method: 'put',
        data
    });
}

export function putAvailable( data ){
    return fetch({
        url: 'user_banks/available',
        method: 'put',
        data
    });
}
