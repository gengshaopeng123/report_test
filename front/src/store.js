import Vue from "vue";
import Vuex from 'vuex';
Vue.use(Vuex);
let nameVal=localStorage.getItem('username');
let yulanVal=localStorage.getItem('yulanid');
let state={
    name:nameVal,
    yulanid:yulanVal
}
let mutations={
    changeState:(state,val)=>{

        state.name=val
    },
    changeStateyulan:(state,val)=>{
        state.yulanid=val
    },

}
let getter={

}
export default new Vuex.Store({
    state,
    mutations,
    getter,
})