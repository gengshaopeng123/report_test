<template>
  <div class="filterreport">
    <div class="doreporttitle">{{title}}</div>
    <div class="useExplain">
      <div class="explain1">使用说明：</div>
      <div class="explain2">
        <p>1.样本信息为excel表格，一行数据为一个样本的相关信息（此文件必须包含所选注释结果文件对应的样本）</p>
        <p>2.注释结果为excel表格，一个样本对应一个注释结果文件，生成一份报告。</p>
        <p>3.点击生成报告，生成时间大约为1分钟。注：样本信息与注释结果通过“样本编号”作唯一识别。</p>
      </div>
    </div>
    <div class="basemessage">
      <div class='message1'>基本信息：</div>
        <div class='message2'>
           <div>
              <label>
                <span style="color:red">*</span>
                报告类别:</label>
              <el-select  @change="changeOption1"   v-model="value" placeholder="请选择">
              <el-option
                 v-for="item in options"
                :key="item.value"
                :label="item.label"
                :value="item.value"
               >
              </el-option>
              </el-select>
          </div>
         <div>
              <label><span style="color:red">*</span>
                报告模板:</label>
              <el-select v-model="value1" placeholder="请选择">
              <el-option
                 v-for="item in options1"
                :key="item.value1"
                :label="item.label1"
                :value="item.value1">
              </el-option>
              </el-select>
          </div>
       <!-- <div>
          <span style="display:inline-block;vertical-align:top;padding-top:2vh"><span style="color:red">*</span>样本信息:</span>
         <span style="display:inline-block"> 
           <el-upload
            ref="upload"
          class="upload-demo"
          action="/api/filtersite/site"
            :on-preview="handlePreview"
            :on-remove="handleRemove"
            :on-change="changefile"
            multiple
            :limit="1"
            :data='ybxx'
            :on-exceed="handleExceed"
            :auto-upload="false"
            >
            <el-button size="small" type="primary">点击上传</el-button>
          </el-upload>
         </span>
          </div> -->
                  <div>
          <span style="display:inline-block;vertical-align:top;padding-top:2vh"><span style="color:red">*</span>注释结果:</span>
         <span style="display:inline-block"> 
         <el-upload
          ref="upload1"
            class="upload-demo"
            action="/api/filtersite/site"
            multiple
            :data='zsjg'
            :limit="1"
            :auto-upload="false"
            :on-change="changefile1"
            :on-success="successChange"
            :on-remove="handleRemove1"
            >
            <el-button size="small" type="primary">点击上传</el-button>
          </el-upload>
         </span>
          </div>
          
           <div class="sumbitdiv">
             <el-button type="primary" @click="submitMessage">生成并下载文件</el-button>
          </div>

        </div>
    </div>
  </div>
</template>

<script>
import Axios from 'axios'
export default {
  name: 'first',
 data() {
      return {
        title:"过滤文件位点",
        unique:'',
        reportSel:'',
        zsjgg:0,
         options: [],
        options1: [],
        value: '',
        geshi:0,
        value1:'',
        ybxx:{
          unique:"",
          type:1,
        },
        zsjg:{
          unique:'',
          type:2,
        },
      }
    },
    created(){
 this.http.get('/api/reportclassify/allclass',{}).then((data)=>{
          console.log(data)
            this.reportSel=data.data.data;
            for(let i=0;i<this.reportSel.length;i++){
              let obj={
                  value: '',
                  label: ''
              }
               this.options.push(obj)
              this.options[i].value=this.reportSel[i].id;
              this.options[i].label=this.reportSel[i].name
            }
          },(err)=>{
            console.log(err)
          })
    },
    methods:{
       submitMessage(){
          if(this.value==10003){
                  this.$message({
          message: '实体肿瘤不需要过滤位点！',
          type: 'warning'
        });
        return
          }

          if(this.value==''||this.value==''||this.zsjgg==0){
              this.$message.warning(`请选择或上传完成必填项！`);
              return
          }
            if(this.geshi==1){
              this.$message.warning(`注释结果请上传Excel文件`);
              return
          }
          let unique=Math.floor((1+Math.random())*10000000000000000);
          this.unique=unique
          console.log(unique)
            this.ybxx.unique=unique;
            this.zsjg.unique=unique;
        // this.$refs.upload.submit();
        this.$refs.upload1.submit();
    
      },
          handleRemove(file, fileList) {
        console.log(file),fileList;
      },
        changefile(file){
        console.log(file)  
      },
       changefile1(file,fileList){
        console.log(file,fileList)
        if(file.raw.type=="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"||file.raw.type=="application/vnd.ms-excel"){
          this.geshi=0;
        }else{
           this.geshi=1;
        }
        this.zsjgg=1;
       
      },
      successChange(response, file, fileList){
        console.log(file)
        console.log(this.value1)
        const loading = this.$loading({
          lock: true,
          text: 'Loading',
          spinner: 'el-icon-loading',
          background: 'rgba(0, 0, 0, 0.7)'
        });
           this.http.post('/api/filtersite/site',
           {
            username:this.$store.state.name,
            report_type:this.value,
            report_template:this.value1,
            unique:this.unique
           }).then((data)=>{
             console.log(data)
                if(data.data.data==''){
                  loading.close();
           this.$message({
          message: '请稍等，数据正在加载中...',
          type: 'warning'
        });
        }else{
       loading.close();
       console.log("进来了")
        var link = document.createElement('a')
        link.target = '_blank'
        link.download="过滤文件.xlsx"
        console.log(data.data.data)
        
        link.href = data.data.data
        link.click()
        }
          },(err)=>{
            loading.close();
            console.log(err)
          })
      },
      handleRemove1(){
       this.zsjgg=0;
      this.geshi=0;
      },
         handlePreview(file) {
        console.log(file);
      },
      handleExceed(files, fileList) {
        // this.$refs.upload.submit();
        // this.$refs.upload1.submit();
        // console.log(files,fileList)
        this.$message.warning(`当前限制选择 1 个文件，本次选择了 ${files.length} 个文件，共选择了 ${files.length + fileList.length} 个文件`);
      },
   changeOption1(val){
        this.options1=[];
        this.value1='';
        for(let i=0;i<this.options.length;i++){
        
          if(val==this.options[i].value){
            for(let j=0;j<this.reportSel[i].second.length;j++){
                     let obj={
                value1: '',
                label1: ''  
              }
              this.options1.push(obj);
             console.log(this.reportSel[i].second[j].id,this.reportSel[i].second[j].name)
              this.options1[j].value1=this.reportSel[i].second[j].id
              this.options1[j].label1=this.reportSel[i].second[j].name
              }
          }
        }
        console.log( this.options1)
        console.log(this.value,this.value1)
      }
    },
  mounted(){      
 
  } 
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="less">
#el-collapse-head-5692{
  font-size:16px;
}
.filterreport{
   margin:8px;
  .doreporttitle{
      background-color:#ddd;
      padding: 10px 10px;
      margin-bottom:15px;
      font-weight:bold;
  }
  .useExplain{
    display: flex;
    .explain1{
         font-weight:bold;
         font-size:18px;
    }
    .explain2{
      border: 1px solid #ccc;
      font-size:14px;
      border-radius: 5px;
      padding: 7px 2px;
      p{
        margin:2px;
        color:#776;
      }
    }
  }
  .basemessage{
    margin-top:15px;
    display: flex;
    .message1{
        font-weight:bold;
        font-size:18px;
    }
    .message2{
      div{
        margin-top:15px;
        label{
          font-size:16px;
          font-weight:400;
        }
      }
      .sumbitdiv{
        text-align: center;
        margin-top:80px;
      }
    }
   
  }
}

</style>