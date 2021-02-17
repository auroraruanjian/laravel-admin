import fetch from '@/utils/fetch'

export function getAllRecord( data ) {
    return fetch({
        url: 'user_deposit',
        method: 'get',
        params:data
    });
}

export function applyDeposit( data ) {
    return fetch({
        url: 'user_deposit/apply',
        method: 'post',
        params:data
    });
}
