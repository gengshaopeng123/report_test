<template>
  <div class="first">
    <div class="bgsl firstspan">
        更新日志:
            <p>1.1版本：</p>
            <ul>
              <li>上线</li>
            </ul>
              <p>1.2版本：</p>
            <ul>
              <li>增加心肌病报告自动化</li>
              <li>增加猝死与冠心病防控分子检测报告自动化</li>
              <li>一代图片信息里的变异来源新增”非父源“和"非母源"两个</li>
            </ul>
    </div>
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
        this.http.get('/api/report/num',{username:this.$store.state.name}).then((data)=>{
              console.log(data)
                 if(data.data.code==205){
                 this.$router.push('/login')
              }
              this.bgsl=data.data.data.report_count;
              this.mbsl=data.data.data.template_count;
              this.mksl=data.data.data.module_count;
           
          },(err)=>{
            console.log(err)
          })
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
