import { userLogin, logout,getUserInfo } from '@/api/user';

const setItem = (key, value) => window.localStorage.setItem(key, value);
const userInfoLS = () => {
    return JSON.parse(window.localStorage.getItem('userInfo'));
};

const state = {
    id: 0,
    token: '',
    username: '',
    nickname: '',
    last_ip: '',
    last_time: '',
    draw_time: 0,
    draw_count: 0,
}

const mutations = {
    SET_TOKEN: (state, token) => {
        state.token = token;
        if(typeof token == 'undefined' || token == null || token == '' ){
            window.localStorage.removeItem('token');
        }else{
            window.localStorage.setItem('token', JSON.stringify({
                token: token,
            }));
        }
    },
    SET_USERINFO : ( state , data ) => {
        state.username  = data.username;
        state.nickname  = data.nickname;
        state.last_ip   = data.last_ip;
        state.last_time = data.last_time;
        state.id        = data.id;
    },
    SET_DRAW : ( state , data ) => {
        if( typeof data.draw_time != "undefined" && data.draw_time !=null ){
            state.draw_time  = data.draw_time;
        }
        if( typeof data.draw_count != "undefined" && data.draw_count !=null ) {
            state.draw_count = data.draw_count;
        }
    }
}

const actions = {
    // 用户名登录
    LoginByUsername({ commit }, userInfo) {
        const username = userInfo.username.trim();
        return new Promise((resolve, reject) => {
            userLogin(
                username,
                userInfo.password,
                userInfo.captcha,
                userInfo.code,
                userInfo.real_name
            )
                .then(response => {
                    const data = response.data.data;
                    if (response.data.code == 1) {
                        commit('SET_TOKEN',data.token)
                    }
                    resolve(response);
                })
                .catch(error => {
                    reject(error);
                });
        });
    },
    getUserInfo({commit}){
        return new Promise((resolve, reject) => {
            getUserInfo().then( (response) => {
                if( response.data.code == 1 ){
                    commit('SET_USERINFO', response.data.data);
                    commit('SET_DRAW', response.data.data);
                }

                resolve(response.data.data)
            }).catch(error => {
                reject(error)
            })
        })
    },
    resetToken({commit}){
        return new Promise(resolve => {
            commit('SET_TOKEN', '');
            commit('SET_USERINFO', []);
            resolve()
        })
    },
    // 登出
    logout({ commit, state }) {
        return new Promise((resolve, reject) => {
            logout().then( (response) => {
                this.dispatch('user/resetToken');
                resolve(response)
            }).catch(error => {
                reject(error)
            })
        })
    },
}

export default {
    namespaced: true,
    state,
    mutations,
    actions
}
