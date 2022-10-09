<template>
  <div class="finishreport">
    <div class="finishreporttitle">{{title}}</div>
     <el-table
      stripe
       border
      :data="tableData"
      v-loading="loading"
       element-loading-text="拼命加载中"
       element-loading-spinner="el-icon-loading"
      style="width: 100%">
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
        label="报告类型">
      </el-table-column>
        <el-table-column
        prop="reportClassify.name"
        align="center"
        label="报告名称"
        width="500px">
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
        prop="ctime"
          align="center"
        label="报告生成时间"
         width="300px">
      </el-table-column>
        <el-table-column
        prop="status"
        align="center"
        label="状态"
      filter-placement="bottom-end">
      <template slot-scope="scope">
        <el-tag
          :type="scope.row.status === '审核通过' ? 'success' :''"
          disable-transitions>{{scope.row.status}}</el-tag>
      </template>
      </el-table-column>
        <el-table-column
          align="center"
        label="操作">
      <template slot-scope="scope">
        <!-- <el-button
          size="mini"
           type="primary"
          @click="handleEdit(scope.$index, scope.row)">编辑</el-button> -->
        <el-button
          size="mini"
          @click="handleDelete(scope.$index, scope.row)">详情</el-button>
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
  name: 'first',
 data() {
      return {  
        
        title: "已通过报告",
        tableData: [],
        totPagenum:0,
        curPage:1,
        loading:true
      }
    },
    methods:{

      handleDelete(a,b){
         console.log(a,b)
         console.log(this.$router)
        this.$store.state.yulanid=b.id;
         this.$store.commit("changeStateyulan",b.id)
         localStorage.setItem("yulanid",b.id)
         this.$router.push("/index/Mangent/reportdetail")
       },
      changePage(val){
      console.log(val)
      this.curPage=val;
      this.getTable();
    },
    getTable(){
        this.loading=true
    this.http.get('/api/report/reportlist',{username:this.$store.state.name,page:this.curPage,status:1}).then((data)=>{
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
    }
      },
    created(){
      console.log(this.$store.state.name)
      this.getTable();
    },
  mounted(){      
 
  } 
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="less">
  .finishreport{
    margin:8px;
    .finishreporttitle{
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