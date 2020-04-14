import fetch from '@/utils/fetch'

export function getActivity( id ) {
    return fetch({
        url: '/activity/',
        method: 'get',
        param:{id:id}
    });
}

export function getDraw() {
    return fetch({
        url: '/activity/draw',
        method: 'get',
    });
}

export function getRecord( data ) {
    return fetch({
        url: '/activity/record',
        method: 'post',
        data
    });
}

export function getRankList( ) {
    return fetch({
        url: '/activity/rankList',
        method: 'get',
    });
}
