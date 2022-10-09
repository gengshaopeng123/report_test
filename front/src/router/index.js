import Vue from 'vue'
import Router from 'vue-router'
import Mangent from '@/components/indexComponent/Mangent'
import login from '@/components/login'
import index from '@/components/index'
import error from '@/components/error'
import first from '@/components/indexComponent/first'
import waitreport from '@/components/indexComponent/waitreport'
import finishreport from '@/components/indexComponent/finishreport'
import doreport from '@/components/indexComponent/doreport'
import filtersite from '@/components/indexComponent/filtersite'
import reportdetail from '@/components/indexComponent/reportdetail'
import deletereport from '@/components/indexComponent/deletereport'
import handlereport from '@/components/indexComponent/handlereport'
import rizhi from '@/components/indexComponent/rizhi'
import store from '../store'
import mkmanagement from '@/components/indexComponent/Module management/index'
import mbmanagement from '@/components/indexComponent/TemplateManagement/index'
import { throws } from 'assert'
Vue.use(Router)

var Router1= new Router(
  {
    mode:'history',
    routes: [
      {
        path: '/',
        redirect:"/login"
      },
      {
        path: '',
        redirect:"/login"
      },
      {
        path: '/login',
        name:'login',
        component: login
      },
    
      {
        path: '/index',
        name: 'index',
        component: index,
        redirect:'/index/first',
        children:[
          {    
              path: '/index/first',
              name: 'first',
              component: first
          },
          {
            path:'/index/mkmanagement',
            name:'mkmanagement',
            component:mkmanagement
        },
        {
          path:'/index/mbmanagement',
          name:'mbmanagement',
          component:mbmanagement
       },
     
          {
            path: '/index/Mangent',
            name: 'Mangent',
            component: Mangent,
            redirect:"/index/Mangent/waitreport",
            children:[
              {
                path: '/index/Mangent/waitreport',
                name: 'waitreport',
                component: waitreport,
              },
              {
                path: '/index/Mangent/handlereport',
                name: 'handlereport',
                component: handlereport,
              },
              {
                path: '/index/Mangent/finishreport',
                name: 'finishreport',
                component: finishreport,
              },
              {
                path: '/index/Mangent/deletereport',
                name: 'finishreport',
                component: deletereport,
              },
              {
                path: '/index/Mangent/doreport',
                name: 'doreport',
                component: doreport,
              },
              {
                path: '/index/Mangent/filtersite',
                name: 'filtersite',
                component: filtersite,
              },
              {
                path: '/index/Mangent/reportdetail',
                name: 'reportdetail',
                component: reportdetail,
              },
              {
                path:'/index/rizhi',
                name:'rizhi',
                component:rizhi
            },
            ]
          }
        ]
      }, 
      {
        path:'*',
        name:'error',
        component:error
      }
    ]
  }
)

Router1.beforeEach((to, from, next) => {
 if(to.fullPath=="/login"){
   next()
 }
  if(store.state.name==''||store.state.name==null||store.state.name==undefined){
    next('/login')
  }else{
    next()
  }
 
})
export default Router1

