import Vue from 'vue'


import App from '@/views-m/app.vue';
import router from '@/router-m';
import store from '@/store';

window.Vue = require('vue');

require('./bootstrap');

import { NavBar,Button,Popup,Icon,Toast,Notify,Checkbox,List,Image,Row,Col,Dialog,Form,Field } from 'vant';
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

Vue.prototype.$eventBus = new Vue();

new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App),
    beforeCreate() {

    },
    methods:{
        /*
        //参数：消息，待选列表，当前已选项(高亮)，回调函数，是否可以按遮罩层关闭，是否加宽，增设class样式名
        alertSelect(msg,list = ['开启', '关闭'],curindex = 1,callback = val => {},cancelable = false,wplus = false,spclass = ''){
            this.$ons.notification
                .alert({
                    modifier: wplus ? 'select wplus' : 'select',
                    title: '',
                    class: spclass,
                    message: msg,
                    buttonLabels: list,
                    primaryButtonIndex: curindex,
                    cancelable: cancelable
                })
                .then(val => {
                    callback(val);
                });
        },
        //type 可选 success error warning
        //参数：消息，标题，类型，是否html，回调函数，按钮(多个按钮用数组,回调里的val对应按下按钮的索引)
        alertMessage(msg, tit = '', type = '', isHtml = false, callback = val => {},buttonLabels = '确定') {
            this.$ons.notification
                .alert({
                    cancelable: type ? false : true,
                    modifier: type,
                    message: isHtml ? '' : msg,
                    messageHTML: isHtml ? msg : '',
                    title: tit,
                    buttonLabels: buttonLabels
                })
                .then(val => {
                    callback(val);
                });
        },
        //type 可选 success error warning , 可叠加 (空格)small 用来一行显示更多的字
        alertToast(msg, type = '',force = false) {
            this.$ons.notification.toast({
                modifier: '',
                message: msg,
                animation: 'fall',
                timeout: 2000,
                force: force
            });
        },
        */
    }
});
