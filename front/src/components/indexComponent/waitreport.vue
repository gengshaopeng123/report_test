<template>
  <div class="waitreport">
    <div class="waitreporttitle">{{title}}</div>
     <el-table
         v-loading="loading"
       element-loading-text="拼命加载中"
       element-loading-spinner="el-icon-loading"
      stripe
       border
      :data="tableData"
      style="width: 100%">
      <el-table-column
     
      align="center"
        prop="xuhao"
        label="#"
        width="60">
      </el-table-column>
      <el-table-column
        prop="sample_name"
        align="center"
        label="样本名称"
        width="150px"
     >
      </el-table-column>
      <el-table-column
        prop="type_name"
        align="center"
        label="报告类型">
      </el-table-column>
        <el-table-column
        prop="reportClassify.name"
        align="center"
        label="报告名称"
        width="400px">
      </el-table-column>
        <el-table-column
        prop="user_name"
        align="center"
        label="姓名"
        >
      </el-table-column>
        <el-table-column
        prop="sex"
          align="center"
        label="性别"
        >
      </el-table-column>
        <el-table-column
        prop="ctime"
          align="center"
        label="报告生成时间"
        width="200px">
      </el-table-column>
        <el-table-column
        prop="status"
        align="center"
        label="状态"
      filter-placement="bottom-end">
      <template slot-scope="scope">
        <el-tag
          :type="scope.row.status === '待审核' ? 'primary' :''"
          disable-transitions>{{scope.row.status}}</el-tag>
      </template>
      </el-table-column>
        <el-table-column
          align="center"
          width="240"
        label="操作">
      <template slot-scope="scope">
        <el-button
          size="mini"
           type="primary"
          @click="handleEdit(scope.$index, scope.row)">审核</el-button>
        <el-button
          size="mini"
          @click="handleDelete(scope.$index, scope.row)">详情</el-button>
        <el-button
          size="mini"
          type="success"
          @click="handledownload(scope.$index, scope.row)">下载</el-button>
      </template>
      </el-table-column>
    </el-table>
    <div class="pagin">
    <el-pagination
     background
  layout="prev, pager, next"
  @current-change="changePage"
   :total="totPagenum"
   :page-size="20">
</el-pagination>
    </div>

  </div>
 
</template>

<script>
import Axios from 'axios'
export default {
 data() {
      return {  
        
        title: "待审核报告",
        tableData: [],
        curPage:1,
        totPagenum:0,
         loading: true
      }
    },
     methods:{
       handleDelete(a,b){
         console.log(a,b.id)
         this.$store.state.yulanid=b.id;
         this.$store.commit("changeStateyulan",b.id)
         localStorage.setItem("yulanid",b.id)
         this.$router.push("/index/Mangent/reportdetail")
       },
       handledownload(a,b){

        if(b.download_path==''){
           this.$message({
          message: '请稍等，数据正在加载中...',
          type: 'warning'
        });
        return
      }else{
      var link = document.createElement('a')
        link.target = 'blank'
        link.download="自动化报告.docx"
        link.href = b.download_path
        console.log(link)
        link.click();
      }
       },
      changePage(val){
      console.log(val)
      this.curPage=val;
      this.getTable();
    },
    getTable(){
      this.loading=true;


  
    this.http.get('/api/report/reportlist',{username:this.$store.state.name,page:this.curPage,status:0}).then((data)=>{
              console.log(data)
            if(data.data.code==205){
                 this.$router.push('/login')
              }
          this.tableData=data.data.data.reportList;
          this.totPagenum=data.data.data.count;
          this.loading=false;
          console.log(this.totPagenum)
          },(err)=>{
            console.log(err)
               this.loading=false
          })
    },
    handleEdit(a,b){
      this.$confirm('此操作将永久更改该报告, 是否继续?', '提示', {
         distinguishCancelAndClose: true,
          confirmButtonText: '通过',
          cancelButtonText: '未通过',
          type: 'warning'
        }).then(() => {
             
             this.http.post('/api/report/examine',{username:this.$store.state.name,id:b.id,status:1}).then((data)=>{
              console.log(data)
            this.$message({
            type: 'success',
            message: '更改成功!'
          });
          this.$router.push('/index/Mangent/finishreport')
          },(err)=>{
            console.log(err)
          })
   
        }).catch((action) => {
          if(action=="close"){

          }else{
      this.http.post('/api/report/examine',{username:this.$store.state.name,id:b.id,status:2}).then((data)=>{
              console.log(data)
            this.$message({
            type: 'success',
            message: '更改成功!'
          });
           this.$router.push('/index/Mangent/deletereport')
          },(err)=>{
            console.log(err)
          })
          }
         
        });
      console.log(a,b)
    }
      },
    created(){
      this.getTable();
    },
    beforeMount(){
   
      },
    mounted(){      

  } 
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="less">
  .waitreport{
    margin:8px;
    .waitreporttitle{
      background-color:#ddd;
      padding: 10px 10px;
      margin-bottom:15px;
      font-weight:bold;
    }
    .pagin{
      margin-top:30px;
      text-align: center;
    }
  }
</style>