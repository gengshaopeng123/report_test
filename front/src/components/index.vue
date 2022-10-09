<template>
<div class="indexTitle">
 <div class="title">
  <el-menu :default-active="activeIndex" class="el-menu-demo" mode="horizontal"
   @select="handleSelect" background-color="#324157" 
   text-color="#bfcbd9"
   active-text-color="#20a0ff">
   <el-col :span= "4" style="text-align:center">
<span class="logotitle"  @click="pushrizhi">{{TITLE}}<el-badge value="new" class="item">
</el-badge></span></el-col>
  <el-menu-item index="1"><i class="el-icon-s-home"></i>首页</el-menu-item>
  <el-menu-item index="2">报告管理</el-menu-item>
  <el-menu-item index="3">模板管理</el-menu-item>
  <el-menu-item index="4">模块管理</el-menu-item>
  <el-menu-item index="5" style="float:right">
   系统设置<i class="el-icon-setting"></i>
   </el-menu-item>
  <el-dropdown class="rightbtn">
  <el-button type="primary">
  遗传咨询师 {{username}}<i class="el-icon-arrow-down el-icon--right"></i>
  </el-button>
  <el-dropdown-menu slot="dropdown">
    <el-dropdown-item><i class="el-icon-key"></i>修改密码</el-dropdown-item>
    <el-dropdown-item><i class="el-icon-switch-button"></i><span @click="tuichu">退出系统</span></el-dropdown-item>
  </el-dropdown-menu>
</el-dropdown>
  
</el-menu>
 </div>
 <div class="container1">
    <router-view/>
 </div>
 
  </div>
</template>

<script>
import Axios from 'axios'
export default {
  name: 'first',
 data() {
      return {
        activeIndex: '1',
        TITLE:'自动化报告系统',
        username:this.$store.state.name
      }
    },
    methods:{
    handleSelect(key, keyPath) {
        console.log(key, keyPath);
        if(key==1){
              this.$router.push('/index/first')
        }else if(key==2){
              this.$router.push('/index/Mangent')
        }else if(key==3){
              this.$router.push('/index/mbmanagement')
        }else if(key==4){
              this.$router.push('/index/mkmanagement')
        }else if(key==5){
              this.$router.push('/index/first')
        }
      },
      pushrizhi(){
          this.$router.push('/index/rizhi')
      },
      tuichu(){
          this.$router.push('/login')
      }
    },
    created(){
      console.log(this.$route.path.indexOf('first'))
      if(this.$route.path.indexOf('first')!==-1){
        this.activeIndex="1"
      }else if(this.$route.path.indexOf('index/Mangent')!==-1){
        this.activeIndex="2"
      }else if(this.$route.path.indexOf('index/mbmanagement')!==-1){
        this.activeIndex="3"
      }else if(this.$route.path.indexOf('index/mkmanagement')!==-1){
        this.activeIndex="4"
      }else{
        this.activeIndex="0"
      };
    },
  mounted(){      
 
  } 
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="less">
.indexTitle{
  .title{
    position: relative;
  }
  .el-menu-demo{
    .logotitle{
    color:#fff;
    font-size: 18px;
    line-height: 61px;
    vertical-align: middle;
    float: left;
    text-align: center;
    width:100%;
    cursor: pointer;
    }
    li{
      margin-right:20px;
     
    }
    .rightbtn{
      float: right;
      margin-right: 20px;
    }
    button{
      padding:0 20px;
      margin-right: 20px;
      line-height: 61px;
      background-color: #324157;
      border-color: #324157
    }
    .righrfl{
      float: right;
      margin-right: 20px;
    }
 
  }
  .container1{
    position: relative;
  }
 
}
</style>
