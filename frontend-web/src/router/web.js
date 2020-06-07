import Vue from 'vue'
import VueRouter from 'vue-router'
import NProgress from 'nprogress' // progress bar
import store from '@/store';

Vue.use(VueRouter)

NProgress.inc(0.2)
NProgress.configure({ easing: 'ease', speed: 500, showSpinner: false })

import Index from '../views-web/index/Index.vue'

const routes = [
    {
        path: '/',
        name: 'Index',
        component: Index
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views-web/user/login.vue')
    },
]

const router = new VueRouter({
    routes
})

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
                    //console.log(e);
                    store.dispatch('user/resetToken').then(()=>{
                        next({path:'/'});
                    })
                }
            }
        }

    }else{
        if( to.path === '/login' || to.path === '/' ){
            next();
        }else{
            next({path:'/'});
        }
    }
});

router.afterEach(() => {
    NProgress.done()
})

export default router
