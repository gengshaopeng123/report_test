<template>
 <div class="wrapper" ng-class="{'form-success':ctrl.showlogin}">
    <div class="container">
        <h1>自动化报告系统</h1>
        <div class="form">
            <input type="text" v-model="userid" placeholder="用户名">
            <input type="password" v-model="password" placeholder="密码">
            <button type="submit" id="login-button" @click="login()">登陆</button>
        </div>
    </div>
</div>
</template>

<script>
import Axios from 'axios'
export default {
  name: 'first',
 data() {
      return {
          userid:'',
          password:'',

      }
    },
  mounted(){      

  } ,
  methods:{
    login(){
           this.http.get('/api/sign/login',{username:this.userid,password:this.password}).then((data)=>{
          console.log(data)
          },(err)=>{
            console.log(err)
          })
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.wrapper{
  background: #223131;
  background: -webkit-linear-gradient(top left, #50a3a2 0%, #53e3a6 100%);
  background: linear-gradient(to bottom right, #50a3a2 0%, #53e3a6 100%);
  position: absolute;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  margin: 0;
  display: block;
  top: 0;
}
.container {
  max-width: 600px;
  margin: 0 auto;
  padding: 50px 0;
  height: 400px;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.4);
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  z-index:2;
  position: relative;
  top:50%;
  margin-top:-250px;

}
.container:hover {
 border-color:#53e3a6;

}
.container h1 {
  color: #fff;
  font-size: 29px;
  position: relative;
  z-index: 3;
}
.form input {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  outline: 0;
  border: 1px solid rgba(255, 255, 255, 0.4);
  background-color: rgba(255, 255, 255, 0.2);
  width: 250px;
  border-radius: 3px;
  padding: 10px 15px;
  margin: 0 auto 15px auto;
  display: block;
  text-align: center;
  font-size: 18px;
  color: white;
  -webkit-transition-duration: 0.25s;
  transition-duration: 0.25s;
  font-weight: 300;
}

.form input:hover {
  background-color: rgba(255, 255, 255, 0.4);
}

.form input:focus {
  background-color: white;
  width: 300px;
  color: #53e3a6;
}

.form button {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  outline: 0;
  background-color: white;
  border: 0;
  padding: 10px 15px;
  color: #53e3a6;
  border-radius: 3px;
  width: 250px;
  cursor: pointer;
  font-size: 18px;
  -webkit-transition-duration: 0.25s;
  transition-duration: 0.25s;
}

.form button:hover {
  background-color: #f5f7f9;
}
</style>
