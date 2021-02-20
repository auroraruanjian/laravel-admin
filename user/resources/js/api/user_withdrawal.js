import fetch from '@/utils/fetch'

export function getAllRecord( data ) {
    return fetch({
        url: 'user_withdrawal',
        method: 'get',
        params:data
    });
}

export function applyWithdrawal( data ) {
    return fetch({
        url: 'user_withdrawal/apply',
        method: 'post',
        data
    });
}

