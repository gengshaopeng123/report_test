<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>
<style lang="less" >
#app{
  font-family: "Helvetica Neue",Helvetica,"PingFang SC","Hiragino Sans GB","Microsoft YaHei","微软雅黑",Arial,sans-serif;
    .ydylinput{
      width:100px;
      .el-input__inner{
        width:100px!important;
       height:40px!important;
       line-height:40px!important;
      }
    }
        .ydylinput2{
      width:150px;
      .el-input__inner{
        width:150px!important;
       height:40px!important;
       line-height:40px!important;
      }
    }
  //   .el-input__inner{
  //      width:250px!important;
  //      height:30px!important;
  //      line-height:30px!important;
  // }
    // .el-input__suffix{
    //   height:30px!important;
    // }
    // .el-input__icon{
    //   line-height:40px;
    // }
    .el-collapse-item__header{
      font-size:18px;
      color:#409eff;
      width:600px;
      // border:none;
    }
    .el-collapse-item__arrow{
      margin:0!important;
      border:none;
    }
}

</style>
