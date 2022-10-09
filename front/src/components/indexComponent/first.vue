<template>
  <div class="first">
    <div class="bgsl firstspan">
      <img src="../../assets/1.png" alt="">
      <div class="firstnumtitle">报告数量</div>
      <div class="firstnum">{{bgsl}}</div>
    </div>
    <div class="mbsl firstspan">
       <img src="../../assets/2.png" alt="">
      <div class='firstnumtitle'>模板数量</div>
      <div class="firstnum">{{mbsl}}</div>
    </div>
    <div class="mksl firstspan">
       <img src="../../assets/3.png" alt="">
      <div class='firstnumtitle'>模块数量</div>
      <div class="firstnum">{{mksl}}</div>
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
    console.log(this.$store.state.name)
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
  vertical-align: middle;
  text-align: center;
  margin-top:200px;
    .firstspan{
      position: relative;
      margin-right:60px;
      display:inline-block;
      img{
        position:relative;
        display: inline-block;
      }
      .firstnumtitle{
        position: relative;
        margin:10px 0px;
      }

    }
}
</style>
