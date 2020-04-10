import fetch from '@/utils/fetch'

export function deleteDeleteFile( data ) {
    return fetch({
        url: 'upload/delete',
        method: 'delete',
        data
    });
}
