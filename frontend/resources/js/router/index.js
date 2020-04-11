import Vue from 'vue';
import VueRouter from 'vue-router';
import {Message} from 'element-ui';
import NProgress from 'nprogress' // progress bar
import 'nprogress/nprogress.css' //这个样式必须引入
import store from '@/store';

// import index from '@/views/default/index';

Vue.use(VueRouter);

NProgress.inc(0.2)
NProgress.configure({ easing: 'ease', speed: 500, showSpinner: false })


/* Layout */
import layout from '@/views/layout'

import index from '@/views/index/index';

// 懒加载组件
const login = () => import('@/views/user/login'),
      page404 = () => import('@/views/error/404');

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
    ]
});

router.beforeEach(async (to, from, next) =>  {
    let tokenStore = JSON.parse(window.localStorage.getItem('token'));

    if( tokenStore ){
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

router.afterEach(() => {
    NProgress.done()
})

export default router
