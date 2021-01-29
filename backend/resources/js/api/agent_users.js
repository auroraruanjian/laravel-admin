import fetch from '@/utils/fetch'

export function getAllAgentUsers( data ) {
    return fetch({
        url: 'agent_users',
        method: 'post',
        params:data
    });
}

export function addAgentUsers( data ){
    return fetch({
        url: 'agent_users/create',
        method: 'post',
        data
    });
}

export function getAgentUsers( id ){
    return fetch({
        url: 'agent_users/edit',
        method: 'get',
        params:{id:id}
    });
}

export function editAgentUsers( data ){
    return fetch({
        url: 'agent_users/edit',
        method: 'put',
        data
    });
}

export function deleteAgentUsers( id ){
    return fetch({
        url: 'agent_users/delete',
        method: 'delete',
        params:{id:id},
    });
}
