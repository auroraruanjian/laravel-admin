import fetch from '@/utils/fetch'

export function getActivitys( data ) {
    return fetch({
        url: 'activity/index',
        method: 'post',
        data
    });
}

export function postCreate( data ) {
    return fetch({
        url: 'activity/create',
        method: 'post',
        data
    });
}

export function getEdit( id ) {
    return fetch({
        url: 'activity/edit',
        method: 'get',
        params:{id}
    });
}

export function putEdit( data ) {
    return fetch({
        url: 'activity/edit',
        method: 'put',
        data
    });
}
