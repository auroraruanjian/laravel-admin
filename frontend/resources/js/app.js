import Vue from 'vue'

import App from '@/views/app.vue';
import router from '@/router';
import store from '@/store';
// import VueCookie from 'vue-cookie' ;
// Vue.use(VueCookie);

// import 'normalize.css/normalize.css' // a modern alternative to CSS resets
// import ElementUI from 'element-ui';
// import 'element-ui/lib/theme-chalk/index.css';
// Vue.use(ElementUI);

window.Vue = require('vue');

import scroll from 'vue-seamless-scroll'
Vue.use(scroll)

Vue.prototype.$eventBus = new Vue();


require('./bootstrap');

new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App),
});
