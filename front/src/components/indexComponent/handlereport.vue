<template>
  <div class="waitreport">
    <div class="waitreporttitle">{{title}}</div>
     <el-table
      stripe
       border
      :data="tableData"
       style="width: 100%"
      v-loading="loading"
       element-loading-text="拼命加载中"
       element-loading-spinner="el-icon-loading">
      <el-table-column
      align="center"
        prop="xuhao"
        label="#"
      width="60px">
      </el-table-column>
      <el-table-column
        prop="sample_name"
        align="center"
        label="样本名称"
       >
      </el-table-column>
      <el-table-column
        prop="type_name"
        align="center"
        label="报告类型"
        >
      </el-table-column>
        <el-table-column
        prop="reportClassify.name"
        align="center"
        label="报告名称"
        width="500px">
      </el-table-column>
             <el-table-column
        prop="ctime"
        align="center"
        label="报告创建时间"
        >
      </el-table-column>
        <el-table-column
        prop="user_name"
        align="center"
        label="姓名"
        width="100px">
      </el-table-column>
        <el-table-column
        prop="sex"
          align="center"
        label="性别"
        width="100px">
      </el-table-column>
        <el-table-column
        prop="status"
        align="center"
        label="状态"
         width="200px"
      filter-placement="bottom-end">
      <template slot-scope="scope">
        <el-tag
          :type="scope.row.status === '正在生成中' ? 'warning' :'danger'"
          disable-transitions>{{scope.row.status}}</el-tag>
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
        title: "生成中报告",
        tableData: [],
        curPage:1,
        totPagenum:0,
        loading:true
      }
    },
     methods:{
      changePage(val){
      console.log(val)
      this.curPage=val;
      this.getTable();
    },
    getTable(){
      this.loading=true
    this.http.get('/api/report/reportlist',{username:this.$store.state.name,page:this.curPage,status:3}).then((data)=>{
              console.log(data)
                     if(data.data.code==205){
                 this.$router.push('/login')
              }
          this.tableData=data.data.data.reportList;
          this.totPagenum=data.data.data.count;
            this.loading=false
          console.log(this.totPagenum)
          },(err)=>{
            console.log(err)
               this.loading=false
          })
    },
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