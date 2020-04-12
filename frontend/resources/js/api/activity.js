import fetch from '@/utils/fetch'

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
