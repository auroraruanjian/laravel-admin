import Vue from 'vue'
import router from './router/web.js'
import store from './store'

window.router = router;

import ElementUI from 'element-ui';
// import 'element-ui/lib/theme-chalk/index.css';

import App from './views-web/App.vue'

Vue.use(ElementUI);

Vue.config.productionTip = false

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
