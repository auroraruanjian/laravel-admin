import fetch from '@/utils/fetch'

export function getAllMethod( data ) {
    return fetch({
        url: 'user_pledge',
        method: 'get',
        params:data
    });
}

export function getCreate(flag, parent_id) {
    return fetch({
        url: 'user_pledge/create',
        method: 'get',
        params: {
            flag: flag,
            parent_id: parent_id
        }
    });
}

export function addMethod( data ){
    return fetch({
        url: 'user_pledge/create',
        method: 'post',
        data
    });
}

export function putIsOpen( data ){
    return fetch({
        url: 'user_pledge/isopen',
        method: 'put',
        data
    });
}

export function putAvailable( data ){
    return fetch({
        url: 'user_pledge/available',
        method: 'put',
        data
    });
}
