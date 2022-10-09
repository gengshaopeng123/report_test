<template>
  <div class="first">
      模块管理正在开发中。。。。
  </div>
</template>

<script>
import Axios from 'axios'
export default {
  name: 'first',
 data() {
      return {
        value: '',
        bgsl:0,
        mbsl:0,
        mksl:0,
      }
    },
  mounted(){      
   
  },
  created(){

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="less">
.first{
  color:#222;
  font-size:24px;
  font-weight: bold;
  text-align: left;
  margin-left:50px;
  margin-top:50px;
  p{
    font-size:18px;
    margin-left:40px;
    margin:3px;
  }
  li{
    font-size:14px;
  }
}
</style>
