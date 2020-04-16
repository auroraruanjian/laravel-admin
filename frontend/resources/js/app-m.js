import Vue from 'vue'


import App from '@/views-m/app.vue';
import router from '@/router-m';
import store from '@/store';

window.Vue = require('vue');

require('./bootstrap');

import { NavBar,Button,Popup,Icon,Toast,Notify,Checkbox,List,Image,Row,Col,Dialog,Form,Field,Swipe,SwipeItem } from 'vant';
Vue.use(NavBar);
Vue.use(Button);
Vue.use(Popup);
Vue.use(Icon);
Vue.use(Toast);
Vue.use(Notify);
Vue.use(Checkbox);
Vue.use(List);
Vue.use(Image);
Vue.use(Row);
Vue.use(Col);
Vue.use(Dialog);
Vue.use(Form);
Vue.use(Field);
Vue.use(Swipe);
Vue.use(SwipeItem);

import scroll from 'vue-seamless-scroll'
Vue.use(scroll)

Vue.prototype.$eventBus = new Vue();

new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App),
    beforeCreate() {

    },
    methods:{

    }
});
