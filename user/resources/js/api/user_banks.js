import fetch from '@/utils/fetch'

export function getAllMethod( data ) {
    return fetch({
        url: 'user_banks',
        method: 'get',
        params:data
    });
}

export function getCreate(flag, parent_id) {
    return fetch({
        url: 'user_banks/create',
        method: 'get',
        params: {
            flag: flag,
            parent_id: parent_id
        }
    });
}

export function addMethod( data ){
    return fetch({
        url: 'user_banks/create',
        method: 'post',
        data
    });
}

export function deleteMethod( id ){
    return fetch({
        url: 'user_banks/delete',
        method: 'delete',
        params:{id:id},
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

export function putChantLimitAmount( data ){
    return fetch({
        url: 'user_banks/chantLimitAmount',
        method: 'put',
        data
    });
}
