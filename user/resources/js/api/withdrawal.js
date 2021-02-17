import fetch from '@/utils/fetch'

export function getAllRecord( data ) {
    return fetch({
        url: 'withdrawal',
        method: 'get',
        params:data
    });
}

export function applyWithdrawal( data ) {
    return fetch({
        url: 'withdrawal/apply',
        method: 'post',
        data
    });
}

