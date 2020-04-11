import fetch from '@/utils/fetch'

export function userLogin(username, password, captcha, code,real_name) {
    const data = {
        username,
        password,
        captcha,
        code,
        real_name
    };
    return fetch({
        url: '/login',
        method: 'post',
        data
    });
}

export function logout() {
    return fetch({
        url: '/logout',
        method: 'post'
    });
}

export function getUserInfo() {
    return fetch({
        url: '/user/info',
        method: 'get'
    });
}

export function modifyPassword(oldpassword, newpassword) {
    return fetch({
        url: '/user/Password',
        method: 'put',
        data: {
            oldpassword: oldpassword,
            newpassword: newpassword
        }
    });
}
