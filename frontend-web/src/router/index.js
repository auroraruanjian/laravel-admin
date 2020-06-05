import Vue from 'vue'
import VueRouter from 'vue-router'
import Index from '../views/Index.vue'
import Login from '../views/user/index.vue'
import NProgress from 'nprogress' // progress bar
import store from '@/store';

Vue.use(VueRouter)

NProgress.inc(0.2)
NProgress.configure({ easing: 'ease', speed: 500, showSpinner: false })

const routes = [
    {
        path: '/',
        name: 'Index',
        component: Index
    },
    {
        path: '/about',
        name: 'About',
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () => import(/* webpackChunkName: "about" */ '../views/About.vue')
    },
    {
        path: '/login',
        name: 'Login',
        component: Login
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
