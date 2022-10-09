import axios from 'axios' 
import Qs from 'qs' 
import { Message } from 'element-ui';  //element库的消息提示，可以不用
//创建axios实例
var service = axios.create({
        headers: {
        'content-type': 'application/x-www-form-urlencoded'
    }
}) 
export default {
    //get请求，其他类型请求复制粘贴，修改method
    get(url1, param,set) {
        console.log(param)
        return new Promise((cback, reject) => {
            service({
                method: 'get',
                url:url1,
                params: param,
                responseType:set
            }).then(res => { 
                var res_code = res.status.toString();
                if (res_code.charAt(0) == 2) {
                    cback(res);   //cback在promise执行器内部
                } else {
                    console.log(res, '异常1')
                }
            }).catch(err => {
                if (!err.response) {
                    Message({
                        showClose: true,
                        message: '请求错误',
                        type: 'error'
                    });
                } else {
                    reject(err.response); 
                    console.log(err.response, '异常2')
                }
            })

        })
    },
    post(url1, param) {
        console.log(param)
        return new Promise((cback, reject) => {
            service({
                method: 'POST',
                url:url1,
                data:Qs.stringify(param),
            }).then(res => { 
                    cback(res);   //cback在promise执行器内部
            }).catch(err => {
                 
                    Message({
                        showClose: true,
                        message: '请求错误',
                        type: 'error'
                    });
                    
            reject(err.response); 
            })

        })
    }
} 