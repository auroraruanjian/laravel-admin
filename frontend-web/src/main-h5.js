import Vue from 'vue'
import router from './router/h5.js'
import store from './store'
import App from './views-h5/App.vue'

window.router = router;

import { Button,Checkbox,Icon,Dialog,Notify  } from 'vant';
Vue.use(Button);
Vue.use(Checkbox);
Vue.use(Icon);
Vue.use(Dialog);
Vue.use(Notify);

Vue.config.productionTip = false

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
