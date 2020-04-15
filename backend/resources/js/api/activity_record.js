import fetch from '@/utils/fetch'

export function getActivityInit(){
    return fetch({
        url: 'activityRecord/init',
        method: 'get',
    });
}

export function getActivityRecord( data ) {
    return fetch({
        url: 'activityRecord/index',
        method: 'get',
        params:data
    });
}

export function putDraw( id ) {
    return fetch({
        url: 'activityRecord/draw',
        method: 'put',
        params:{id:id},
    });
}
