import fetch from '@/utils/fetch'

export function getAllMethod( data ) {
    return fetch({
        url: 'payment_method',
        method: 'get',
        params:data
    });
}

export function addMethod( data ){
    return fetch({
        url: 'payment_method/create',
        method: 'post',
        data
    });
}

export function getMethod( id ){
    return fetch({
        url: 'payment_method/edit',
        method: 'get',
        params:{id:id}
    });
}

export function editMethod( data ){
    return fetch({
        url: 'payment_method/edit',
        method: 'put',
        data
    });
}

export function deleteMethod( id ){
    return fetch({
        url: 'payment_method/delete',
        method: 'delete',
        params:{id:id},
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
