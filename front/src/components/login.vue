<template>
 <div class="wrapper" @keyup.enter="login()" ng-class="{'form-success':ctrl.showlogin}">
    <div class="container">
        <h1>自动化报告系统</h1>
        <div class="form">
            <input type="text" v-model="userid" placeholder="用户名">
            <input type="password" v-model="password" placeholder="密码">
            <button type="submit"  id="login-button" @click="login()">登陆</button>
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
      if(this.userid==''||this.password==''){
        this.$message({
          message: '用户名和密码不能为空',
          type: 'warning'
        });
        return
      }
           this.http.post('/api/sign/login',{
             username:this.userid,
             password:this.password
           }).then((data)=>{
          console.log(data)
          if(data.data.code==200){
              this.$store.commit("changeState",this.userid)
              localStorage.setItem("username",this.userid)
              this.$router.push("/index/first")
          }else{
            this.$message({
          message:data.data.msg,
          type: 'error'
        });
          }
          },(err)=>{
          this.$message({
          message: err,
          type: 'error'
        });
          })
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
 input::-webkit-input-placeholder{
            color:#fff;
        }
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
    min-width: 600px;
    padding: 65px 0;
    left: 50%;
    transform: translate(-50%,-50%);
    text-align: center;
    border: 1px solid hsla(0,0%,100%,.4);
    background-color: hsla(0,0%,100%,.2);
    border-radius: 20px;
    z-index: 2;
    position: absolute;
    top: 50%;
    margin:0;
    color: #fff;

}
.container:hover {
 border-color:#53e3a6;

}
.container h1 {
    color: #fff;
    font-size: 29px;
    position: relative;
    z-index: 3;
    font-weight: 300;
}
.form input {
     -webkit-appearance: none;
    -moz-appearance: none;
    outline: 0;
    border: 1px solid hsla(0,0%,100%,.4);
    background-color: hsla(0,0%,100%,.2);
    width: 250px;
    border-radius: 3px;
    padding: 10px 15px;
    margin: 0 auto 15px;
    display: block;
    text-align: center;
    font-size: 18px;
    color: #fff;
    transition-duration: .25s;
    font-weight: 300;
    box-sizing: border-box;
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
    outline: 0;
    background-color: #fff;
    border: 0;
    padding: 10px 15px;
    color: #53e3a6;
    border-radius: 3px;
    width: 250px;
    cursor: pointer;
    font-size: 18px;
    transition-duration: .25s;
    box-sizing: border-box;
    margin: 0;
    font-weight: 300;
}

.form button:hover {
  background-color: #f5f7f9;
}
</style>
