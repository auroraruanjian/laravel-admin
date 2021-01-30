import fetch from '@/utils/fetch'

export function getAllAgent( data ) {
    return fetch({
        url: 'child_agent',
        method: 'post',
        params:data
    });
}

export function addAgent( data ){
    return fetch({
        url: 'child_agent/create',
        method: 'post',
        data
    });
}

export function getAgent( id ){
    return fetch({
        url: 'child_agent/edit',
        method: 'get',
        params:{id:id}
    });
}

export function editAgent( data ){
    return fetch({
        url: 'child_agent/edit',
        method: 'put',
        data
    });
}

export function deleteAgent( id ){
    return fetch({
        url: 'child_agent/delete',
        method: 'delete',
        params:{id:id},
    });
}
