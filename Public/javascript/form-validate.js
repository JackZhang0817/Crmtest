$(function(){
	// 验证真实姓名
//	jQuery.validator.addMethod("realname", function(value, element) {
//	    var real = /^[\u4e00-\u9fa5]{2,4}$/;
//	    return this.optional(element) || (real.test(value));
//	}, "真实姓名只能填写2-4个汉字,不支持英文,数字和标点符号");

	// 验证用户名格式
	jQuery.validator.addMethod("username", function(value, element) {   
	    var user = /^[a-zA-Z][\w]{4,16}$/;
	    return this.optional(element) || (user.test(value));
	}, "以字母开头, 数字相结合, 可使用下划线'_', 长度为5-17位");

	// 验证手机格式
	jQuery.validator.addMethod("tel", function(value, element) {   
	    var tel = /^0?(13[0-9]|15[012356789]|18[0-9]|17[0678]|14[57])[0-9]{8}$/;
	    return this.optional(element) || (tel.test(value));
	}, "手机号码格式不正确");

	// 添加子用户表单验证
	$('#addUser').validate({
		errorElement: 'span', 	  // 默认的错误信息元素
        errorClass: 'help-block', // 默认的错误信息类名
        focusInvalid: false, 	  // 未通过验证的第一个表单元素获得焦点

        // 基础规则验证
        rules : {
        	username : {
        		required : true,
        		username : true,
        		remote : {
					url : checkUsername,
					type : 'post',
					dataType : 'json',
					data : {
						username : function () {
							return $('#username').val();
						}
					}
				}
        	},
        	password : {
        		required : true,
        		username : true
        	},
        	realname: {
                required: true,
//                realname: true
            },
            tel: {
                required: true,
                tel: true,
                remote : {
					url : checkTel,
					type : 'post',
					dataType : 'json',
					data : {
						tel : function () {
							return $('#tel').val();
						}
					}
				}
            },
            //email: {
            //    required: true,
            //    email: true,
            //    remote : {
				//	url : checkEmail,
				//	type : 'post',
				//	dataType : 'json',
				//	data : {
				//		email : function () {
				//			return $('#email').val();
				//		}
				//	}
				//}
            //}
        },

        // 提示信息
        messages : {
        	username : {
        		required : '用户名不能为空',
        		remote : '该用户名已经被注册'
        	},
        	password : {
        		required : '密码不能为空'
        	},
        	realname: {
                required: "真实姓名不能为空."
            },
            tel: {
                required: "联系方式不能为空.",
                remote: "该联系方式已经被注册."
            },
            //email: {
            //    required: "邮箱不能为空.",
            //    email: "请输入正确格式的邮箱地址",
            //    remote: "该邮箱已经被注册."
            //}
        }
	});
});