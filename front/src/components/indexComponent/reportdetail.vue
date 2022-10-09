<template>
  <div class="reportdetail">
     <div class="title">{{title}}
       <!-- <span><el-button icon="el-icon-download" @click="downloaddox">下载</el-button></span> -->
       <!-- <span><el-button icon="el-icon-printer">打印</el-button></span>
       <span><el-button type="primary" icon="el-icon-circle-check">审核</el-button></span> -->
     </div>
      <div class="controlpage">
        <div>
  <p class="arrow">
      <el-button-group>
      <el-button @click="changePdfPage(0)" class="turn" :class="{grey: currentPage==1}" type="primary" icon="el-icon-arrow-left">上一页</el-button>
      <el-button @click="changePdfPage(1)" class="turn" :class="{grey: currentPage==pageCount}" type="primary">下一页<i class="el-icon-arrow-right el-icon--right"></i></el-button>
      </el-button-group>
    </p>
        </div>
  
         {{currentPage}} / {{pageCount}}
      </div>
    <div class="bgyl">
        <pdf 
      :src="src"
      style="width: 100%;" 
      :page="currentPage"
      @num-pages="pageCount=$event" 
      @page-loaded="currentPage=$event"
      @loaded="loadPdfHandler"></pdf>
    </div>
  </div>
</template>

<script>
import Axios from 'axios'
import pdf from 'vue-pdf'
export default {
  name: 'first',
 data() {
      return {
        title:'报告详情',
        currentPage:1,
        pageCount:0,
        download:'',
        src:''
      }
    },
  components:{
    pdf
  },
  methods:{
    downloaddox(){
      if(this.download==''){
        //    this.$message({
        //   message: '请稍等，数据正在加载中...',
        //   type: 'warning'
        // });
        return
      }else{
      var link = document.createElement('a')
        link.target = 'blank'
        link.download="自动化报告.docx"
        link.href = this.download
        console.log(link)
        link.click()
      }
  
    },
  loadPdfHandler (e) {


 

      },
  changePdfPage (val) {
        
        if (val === 0 && this.currentPage > 1) {
          this.currentPage--
     
        }
        if (val === 1 && this.currentPage < this.pageCount) {
          this.currentPage++
          
        }
      },
  },
  mounted(){      
 
  },
  created(){
    console.log("aaa")
       const loading = this.$loading({
          lock: true,
          text: 'Loading',
          spinner: 'el-icon-loading',
          background: 'rgba(0, 0, 0, 0.7)'
        });
    this.http.get('/api/report/reportpdfpath',{id:this.$store.state.yulanid},'blob').then((data)=>{
              console.log(data)
       var blob = new Blob([data.data]);
       this.src=URL.createObjectURL(blob);
        this.currentPage = 1 // 加载的时候先加载第一页
        loading.close();
          },(err)=>{
            console.log(err)
          })

          
      // this.http.get('/api/report/reportpath',{id:this.$store.state.yulanid}).then((data)=>{
      //         console.log(data)
      //   // this.src=data.data.data.pdata;
      //   // this.src = pdf.createLoadingTask(data.data.data.pdata)
      //   // this.src1=this.src
      //   this.download=data.data.data;
      //   // this.currentPage = 1 // 加载的时候先加载第一页
      //     },(err)=>{
      //       console.log(err)
      //     })
         // this.http.get(param, url.downloadObsFiles, (data) => {
          //      var blob = new Blob([data.data], {
          //       type: 'text/plain'
          //       });     
          //       var downloadLink = angular.element('<a></a>');    
 
          //       downloadLink.attr('href', URL.createObjectURL(blob));
                
          //       downloadLink.attr('download', key);
                
          //       downloadLink[0].click();
          //   })

    console.log(this.$store.state.yulanid)
  // this.http.get('/api/report/reportpath',{id:this.$store.state.yulanid}).then((data)=>{
  //             console.log(data)
  //         },(err)=>{
  //           console.log(err)
  //              this.loading=false
  //         })
  },
 
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="less">
  .reportdetail{
    vertical-align: middle;
    margin:8px;
    .title{
      background-color:#ddd;
      height: 40px;
      line-height: 40px;
      padding: 0px 10px; 
      margin-bottom:15px;
      font-weight:bold;
      span{
        float:right;
        margin-left:8px;
        vertical-align: middle;
        button{
          padding: 8px;
        }
      }
    }
    .controlpage{
      text-align: center;
      margin-bottom:20px;
    }
    .bgyl{
      padding: 35px 120px;
      background-color: #ddd;
    }
  }
</style>