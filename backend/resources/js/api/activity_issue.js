import fetch from '@/utils/fetch'

export function getActivityIssue( data ) {
    return fetch({
        url: 'activityIssue/index',
        method: 'post',
        data
    });
}

export function getCreate() {
    return fetch({
        url: 'activityIssue/create',
        method: 'get',
    });
}

export function postCreate( data ) {
    return fetch({
        url: 'activityIssue/create',
        method: 'post',
        data
    });
}

export function getEdit( id ) {
    return fetch({
        url: 'activityIssue/edit',
        method: 'get',
        params:{id}
    });
}

export function putEdit( data ) {
    return fetch({
        url: 'activityIssue/edit',
        method: 'put',
        data
    });
}

export function deleteActivityIssue( id ) {
    return fetch({
        url: 'activityIssue/delete',
        method: 'delete',
        params:{id}
    });
}

export function putOpenCode( data ) {
    return fetch({
        url: 'activityIssue/openCode',
        method: 'put',
        data
    });
}
