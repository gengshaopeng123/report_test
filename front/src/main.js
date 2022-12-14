// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'  //单独引入样式文件
import http from './api/http';
import Vuex from 'vuex';
import store from "../src/store"
Vue.prototype.http=http
Vue.use(ElementUI)
Vue.use(Vuex);
Vue.config.productionTip = false
/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>'
})
