<template>
  <div class="doreport">

    <div>
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
          <div>
          <span style="display:inline-block;vertical-align:top;padding-top:2vh"><span style="color:red">* </span>样本信息:</span>
         <span style="display:inline-block"> 
           <el-upload
            ref="upload"
          class="upload-demo"
          action="/api/generatereport/createreport"
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
          </div>
              <div>
          <span style="display:inline-block;vertical-align:top;padding-top:2vh"><span style="color:red">* </span>注释结果:</span>
         <span style="display:inline-block"> 
         <el-upload
          ref="upload1"
            class="upload-demo"
            action="/api/generatereport/createreport"
            multiple
              :data='zsjg'
                :limit="1"
             :auto-upload="false"
               :on-remove="handleRemove1"
             :on-change="changefile1"
            >
            <el-button size="small" type="primary">点击上传</el-button>
          </el-upload>
         </span>
          </div>
        <div v-show="showyidai" class="fourdiv" v-for="(items,index) in DateList" :key="index+'a'">
          <span style="display:inline-block;vertical-align:top;padding-top:2vh">&nbsp;&nbsp;&nbsp;一代图片:</span>
         <span class="ydyl" :key="index+'c'"> 

          <el-select   v-model="items.qsgx" placeholder="亲属关系">
              <el-option
                 v-for="item in qinshuguanxi"
                :key="item.value"
                :label="item.label"
                :value="item.value"
               >
              </el-option>
              </el-select>
          <!-- <el-input class="ydylinput" v-model="items.qsgx" placeholder="亲属关系" ></el-input> -->
          <el-input class="ydylinput2" v-model="items.gxbz" placeholder="关系备注"></el-input>
          <el-input class="ydylinput2" v-model="items.wd" placeholder="验证位点"></el-input>
          <!-- <el-input class="ydylinput" v-model="items.yzfx" placeholder="验证方向"></el-input> -->
             <el-select    v-model="items.yzfx" placeholder="验证方向">
              <el-option
                 v-for="item in yanzhengfangxiang"
                :key="item.value"
                :label="item.label"
                :value="item.value"
               >
              </el-option>
              </el-select>
                  <el-select    v-model="items.heterozygosity" placeholder="杂合性">
              <el-option
                 v-for="item in zahe"
                :key="item.value"
                :label="item.label"
                :value="item.value"
               >
              </el-option>
              </el-select>
                      <el-select v-show="items.qsgx=='先证者验证'"   v-model="items.variation_source" placeholder="变异来源">
              <el-option
                 v-for="item in bianyi"
                :key="item.value"
                :label="item.label"
                :value="item.value"
               >
              </el-option>
              </el-select>
          <div class="tianjiayidai" v-if="index==DateList.length-1"><i class="el-icon-circle-plus" @click="addarryd(index)"></i></div>
          <div class="tianjiayidai" v-if="index!=DateList.length-1"><i class="el-icon-error" @click="deletearryd(index)"></i></div>
         
          <el-upload 
              ref="upload2"
              class="upload-demo"
              action="/api/generatereport/createreport"
              :on-preview="handlePreview"
             
              :on-change="(file,fileList)=>{handlechange(file,fileList,index)}"
                :data='DateList[index]'
               :auto-upload="false"
               :file-list="items.file_list"
              list-type="picture">
              <el-button size="small" type="primary">点击上传</el-button>
            </el-upload>
            
         </span>
          </div>
          <div class="fivediv">

          <el-collapse v-model="activeNames" @change="handleChange" style="border:none;font-size:16px">
            <el-collapse-item title="高级设置：" name="2">
              <div class="wcwc">
           <div >
                <label>公司名称：</label>
                <el-input  v-model="gsmc" placeholder="请输入内容" ></el-input>
              </div>
            <div>
           <label class="fivelogo">报告logo：</label>
             <el-upload
            ref="upload3"
          class="upload-demo22"
          action="/api/generatereport/createreport"
            :on-preview="handlePreview"
           
            multiple
            :limit="1"
            :data='gslg'
            :on-exceed="handleExceed"
            :auto-upload="false"
            list-type="picture"
            >
            <el-button size="small" type="primary">点击上传</el-button>
          </el-upload>
              </div>
              </div>
         
            </el-collapse-item>
          </el-collapse>

          </div>
          <div class="sixdiv">
             <el-button type="primary" @click="submitMessage">生成报告</el-button>
          </div>
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
        title:"生成报告",
        reportSel:'',
         options: [],
        options1: [],
        gsmc:'',
        bglogo:'',
        ybxxx:0,
        zsjgg:0,
        // ymyj:'',
        value: '',
        showyidai:true,
        value1:'',
        DateList:[{
              qsgx:'',
              yzfx:'',
              gxbz:'',
              wd:'',
              unique:'',
              heterozygosity:'',
              variation_source:'',
              type:3,
              file_list:[],
        }],
        zahe:[
          {
            lable:"纯合",
            value:'纯合'
          },
            {
            lable:"杂合",
            value:'杂合'
          },
            {
            lable:'半合子',
            value:'半合子'
          },
            {
            lable:"体细胞突变",
            value:'体细胞突变'
          },
            {
            lable:"嵌合",
            value:'嵌合'
          },
            {
            lable:"阴性",
            value:'阴性'
          },
        ],
        bianyi:[
          {
            label:"父源",
            value:"父源"
          },
           {
            label:"母源",
            value:"母源"
          },
           {
            label:"父母源",
            value:"父母源"
          }, {
            label:"父或母源",
            value:"父或母源"
          }, {
            label:"新发",
            value:"新发"
          },
           {
            label:"非父源",
            value:"非父源"
          },{
            lable:"非母源",
            value:"非母源"
          }

        ],
        qinshuguanxi:[
          {
            value:"先证者验证",
            label:"先证者验证"
          },
             {
            value:"亲缘验证",
            label:"亲缘验证"
          },
             {
            value:"亲属验证",
            label:"亲属验证"
          },
        ],
        yanzhengfangxiang:[
          {
            value:"正向",
            lable:"+"
          },
          {
            value:"反向",
            lable:"-"
          },
        ],
        activeNames: ['1'],
        ybxx:{
          unique:"",
          type:1,
        },
        zsjg:{
          unique:'',
          type:2,
        },
        gslg:{
          unique:'',
          type:4,
          com_name:'',
        },
        arryd:[1]
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
            if(this.value==''||this.value1==''||this.ybxxx==0||this.zsjgg==0){
                      this.$message.warning(`请选择或上传完成必填项！`);
                     return
            }
              
                    const loading = this.$loading({
          lock: true,
          text: 'Loading',
          spinner: 'el-icon-loading',
          background: 'rgba(0, 0, 0, 0.7)'
        });
          let unique=Math.floor((1+Math.random())*10000000000000000);
          console.log(unique)
            this.ybxx.unique=unique;
            this.zsjg.unique=unique;
            this.gslg.unique=unique;
            for(let i=0;i<this.DateList.length;i++){
                  this.DateList[i].unique=unique;
                  if(this.DateList[i].yzfx=="正向"){
                    this.DateList[i].yzfx="+"
                  }else if(this.DateList[i].yzfx=="反向"){
                   this.DateList[i].yzfx="-"
            }
            }
            this.gslg.com_name=this.gsmc
            // this.ydtp.first_par=this.data,
            // this.ydtp.first_dec=this.yzfx,
            // this.ydtp.first_wd=this.wd,
            // this.ydtp.first_gxbz=this.gxbz,
        this.$refs.upload.submit();
        this.$refs.upload1.submit();
        this.$refs.upload3.submit();
        for(let i=0;i<this.$refs.upload2.length;i++){
               this.$refs.upload2[i].submit();
        }

      console.log(this.DateList)
        setTimeout(()=>{
          console.log(this.value)
        console.log(this.value1)
           this.http.post('/api/generatereport/createreport',
           {
            report_type:this.value,
            report_template:this.value1,
            // com_name:this.gsmc,
            username:this.$store.state.name,
            unique:unique
           }).then((data)=>{
             console.log(data)
              if(data.data.code==200){
                   loading.close();
            this.$emit('func','6')
            this.$router.push('/index/Mangent/handlereport')
              }else if(data.data.code==201){
                     loading.close();
                      alert("提交样本信息和注释结果有问题")
              }
          },(err)=>{
              loading.close();
            console.log(err)
          })
        },6000)
       

      },
        handleRemove(file, fileList) {
        console.log(file),fileList;
        this.ybxxx=0;
      },
          handleRemove1(file, fileList) {
        console.log(file),fileList;
        this.zsjgg=0;
      },
      handlechange(file,fileList,index){
        console.log(fileList)
          if(fileList.length==1){
         this.DateList[index].file_list=fileList
          }else{
            return
          }
       
      },
      addarryd(index){
        console.log(index)
               this.DateList.push({
              qsgx:'',
              yzfx:'',
              gxbz:'',
              wd:'',  
              heterozygosity:'',
              variation_source:'',
              unique:'',
              type:3,
              file_list:[]

        })
   
      },
       openFullScreen2() {
   
    },
      deletearryd(index){
        // let url=this.DateList[this.DateList.length-1].file_list[0].url
          this.DateList.splice(index,1)
   
            console.log(this.DateList)
      },
      changefile(file){
        console.log(file)
        this.ybxxx=1;
        
      },
      changefile1(file,fileList){
        this.zsjgg=1
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
   
      handleChange(val) {
        // console.log(val);
      },
      changeOption1(val){
        console.log(val)
        this.options1=[];
        this.value1='';
        console.log(this.options)
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
        console.log(this.value)
        if(this.value==10001||this.value==10002){
          this.showyidai=true
        }else{
          this.showyidai=false
        }
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
.doreport{
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
      .fourdiv{
        .ydyl{
          color:red;
          display: inline-block;
          position: relative;
            .ydylinput{
           
          }
            .tianjiayidai{
          display: inline-block;
          position: relative;
          color:#ddd;
          font-size:30px;
          height: 65px;
          margin-top:0;
          i{
            height: 65px;
            position: relative;
            top:50%;
            margin-top:-32.5px;
            vertical-align: middle;
            cursor: pointer;
          }
        }
        .el-select{
          width:130px!important;
          height:40px;
          line-height: 40px;
        }
        }
    
      }
      .fivediv{
       
        .el-collapse-item__header{
          color:blue
        }
        .wcwc{
            display: inline-block;
            width:600px;
            div{
              margin-top:10px;
              
           .el-input{
             width:300px;
             height: 25px;
             font-weight: 400;
             font-size:16px;
             margin-top:5px;
        }
            }
      
        }

        .fivelogo{
          display: inline-block;
          margin-top:10px;
          text-align: center;
        vertical-align: top;
        }
        .upload-demo22{
          margin-top:10px;
          position: relative;
          display: inline-block;
          width:300px;
        }
      }
      .sixdiv{
        text-align: center;
      }

    }
   
  }
}

</style>
