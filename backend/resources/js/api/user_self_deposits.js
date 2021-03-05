import fetch from '@/utils/fetch'

export function getDeposits( data ) {
    return fetch({
        url: 'user_self_deposit',
        method: 'get',
        params:data
    });
}

export function getDetail( id ) {
    return fetch({
        url: 'user_self_deposit/detail',
        method: 'get',
        params:{id:id}
    });
}

export function putDeal( data ) {
    return fetch({
        url: 'user_self_deposit/deal',
        method: 'put',
        data
    });
}

export function putVerify( data ) {
    return fetch({
        url: 'user_self_deposit/verify',
        method: 'put',
        data
    });
}


