import Vue from 'vue';
import VueRouter from 'vue-router';
import store from '@/store';

Vue.use(VueRouter);

/* Layout */
import layout from '@/views-m/layout'

import index from '@/views-m/index/index';

// 懒加载组件
const login = () => import('@/views-m/user/login'),
      page404 = () => import('@/views-m/error/404');

const router = new VueRouter({
    routes:[
        {
            path: '/login',
            component: login,
            name:'login'
        },
        {
            path: '/404',
            component: page404,
        },
        {
            path: '/',
            component: layout,
            redirect: '/index',
            children: [
                {
                    path: '',
                    component: index,
                    name: 'index',
                    children: [
                        {
                            path: '/index',
                            component: index
                        }
                    ]
                },
            ]
        }
    ],
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { x: 0, y: 0 };
        }
    }
});

router.beforeEach(async (to, from, next) =>  {
    let tokenStore = window.localStorage.getItem('token');

    if( tokenStore ){
        tokenStore = JSON.parse(tokenStore);

        if (to.path === '/login') {
            next({path:'/'});
        }else{
            const is_login = store.getters.username && store.getters.username.length > 0

            if( is_login ){
                next()
            }else{
                try {
                    await store.dispatch('user/getUserInfo')

                    next({...to, replace: true})
                    //next();
                }catch (e) {
                    console.log(e);
                    store.dispatch('user/resetToken').then(()=>{
                        next({path:'/login'});
                    })
                }
            }
        }

    }else{
        if( to.path === '/login' ){
            next();
        }else{
            next({path:'/login'});
        }
    }
});

export default router
