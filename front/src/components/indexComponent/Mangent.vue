<template>
  <div class="Mangent" >
  <div class="MangentTitle">
  <el-col :span="4">
    <el-menu
      :default-active="curactive"
      class="el-menu-vertical-demo"
      background-color="#545c64"
      text-color="#fff"
       @select="handleSelect"
      active-text-color="#ffd04b">
      <el-menu-item index="1">
        <i class="el-icon-document"></i>
          <span slot="title">{{daohang1}}</span>
      </el-menu-item>
         <el-menu-item index="6">
          <i class="el-icon-document"></i>
        <span slot="title">{{daohang6}}</span>
      </el-menu-item>
      <el-menu-item index="2">
          <i class="el-icon-document"></i>
        <span slot="title">{{daohang2}}</span>
      </el-menu-item>
      <el-menu-item index="3">
          <i class="el-icon-document"></i>
        <span slot="title">{{daohang3}}</span>
      </el-menu-item>
      <el-menu-item index="4">
          <i class="el-icon-document"></i>
        <span slot="title">{{daohang4}}</span>
      </el-menu-item>
         <el-menu-item index="5">
          <i class="el-icon-document"></i>
        <span slot="title">{{daohang5}}</span>
      </el-menu-item>
    </el-menu>
  </el-col>
  </div>
    <div class="content">
      <router-view @func='sendmessage'></router-view>
    </div>

  </div>
</template>
<script>
import Axios from 'axios'
import {mapstate} from 'vuex'
export default {
  // name: 'Mangent',
 data() {
      return {
        daohang1:"待审核报告",
        daohang2:"已审核报告",
        daohang3:"未通过报告",
        daohang4:"生成报告",
        daohang5:"过滤位点文件",
        daohang6:'生成中报告',
        curactive:"1"
      }
    },

  methods:{
    handleSelect(key,keyPath){
        if(key==1){
            this.$router.push('/index/Mangent/waitreport')
        }else if(key==2){
            this.$router.push('/index/Mangent/finishreport')
        }else if(key==4){
            this.$router.push('/index/Mangent/doreport')
        }else if(key==5){
              this.$router.push('/index/Mangent/filtersite')
        }else if(key==3){
              this.$router.push('/index/Mangent/deletereport')
        }else if(key==6){
              this.$router.push('/index/Mangent/handlereport')
        }
    },
    sendmessage(val){
        this.curactive=val
    },
  },
    watch:{
   $route(to,from){
     console.log(to)
        if(to.path.indexOf("handlereport")!=-1){
          console.log("进来了")
           this.curactive="6";
        }
  }
},
  created(){
    if(this.$route.path.indexOf('waitreport')!=-1){
        this.curactive="1";
    }else if(this.$route.path.indexOf("finishreport")!=-1){
      this.curactive="2";
    }else if(this.$route.path.indexOf("deletereport")!=-1){
      this.curactive="3";
    }else if(this.$route.path.indexOf("doreport")!=-1){
      this.curactive="4";
    }else if(this.$route.path.indexOf("handlereport")!=-1){
      this.curactive="6";
    }else if(this.$route.path.indexOf("rizhi")!=-1){
      this.curactive="0";
    }else{
      this.curactive="5";
    }
    // this.$store.commit("changeState","mofashaonv")
    // console.log(this.$store.state.name)
  },
  updated(){
   if(this.$route.path.indexOf('waitreport')!=-1){
        this.curactive="1";
    }else if(this.$route.path.indexOf("finishreport")!=-1){
      this.curactive="2";
    }else if(this.$route.path.indexOf("deletereport")!=-1){
      this.curactive="3";
    }else if(this.$route.path.indexOf("doreport")!=-1){
      this.curactive="4";
    }else if(this.$route.path.indexOf("handlereport")!=-1){
      this.curactive="6";
    }else if(this.$route.path.indexOf("rizhi")!=-1){
      this.curactive="0";
    }else{
      this.curactive="5";
    }
  },
  mounted(){      
   
  } 
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="less">

.Mangent{
  display: flex;
  .MangentTitle{
    width:100%;
    div{
      float:none;
    }
    ul{
      height: 93vh;
      position: relative;
    }
}
.content{
      position: absolute;
      margin-left:16.7776%;
      right:0;
      top:0;
      bottom:0;
      left:0;
      overflow: hidden;
      overflow-y: scroll
    }
}

</style>
