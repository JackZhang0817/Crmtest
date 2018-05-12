// javascript code
// 验证用户名格式
jQuery.validator.addMethod("pass", function(value, element) {
    var pass = /^[a-zA-Z0-9][\s|\S]{4,16}$/;
    return this.optional(element) || (pass.test(value));
}, "以字母开头，数字、符号相结合, 长度为5-17位");

$(function(){
    // 添加子用户表单验证
    $('.form-horizontal').validate({
        errorElement: 'span', 	  // 默认的错误信息元素
        errorClass: 'help-block', // 默认的错误信息类名
        focusInvalid: false, 	  // 未通过验证的第一个表单元素获得焦点

        // 基础规则验证
        rules : {
            oldpassword : {
                required : true
            },
            password : {
                required : true,
                pass : true
            },
            repassword: {
                equalTo: "#password"
            }
        },

        // 提示信息
        messages : {
            oldpassword : {
                required : '原密码不能为空'
            },
            password : {
                required : '新密码密码不能为空'
            },
            repassword: {
                equalTo: "两次输入密码不一致"
            }
        }
    });
});