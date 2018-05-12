//点击刷新验证码
var verifyUrl = $('.verify').attr('src');
$('.verify').click(function () {
	$(this).attr('src', verifyUrl + '?' + Math.random());
});

var Login = function () {

	var handleLogin = function() {
		$('.login-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            rules: {
	                username: {
	                    required: true
	                },
	                password: {
	                    required: true
	                },
	                verify: {
	                	required: true
	                },
	                remember: {
	                    required: false
	                }
	            },

	            messages: {
	                username: {
	                    required: "用户名不能为空."
	                },
	                password: {
	                    required: "密码不能为空."
	                },
	                verify: {
	                	required: "验证码不能为空"
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit   
	                $('.alert-danger', $('.login-form')).show();
	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.form-group').addClass('has-error'); // set error class to the control group
	            },

	            success: function (label) {
	                label.closest('.form-group').removeClass('has-error');
	                label.remove();
	            },

	            errorPlacement: function (error, element) {
	                error.insertAfter(element.closest('.input-icon'));
	            },

	            submitHandler: function (form) {
	                form.submit();
	            }
	        });

	        $('.login-form input').keypress(function (e) {
	            if (e.which == 13) {
	                if ($('.login-form').validate().form()) {
	                    $('.login-form').submit();
	                }
	                return false;
	            }
	        });
	}

	var handleForgetPassword = function () {
		$('.forget-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            ignore: "",
	            rules: {
	                email: {
	                    required: true,
	                    email: true
	                }
	            },

	            messages: {
	                email: {
	                    required: "邮箱不能为空."
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit   

	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.form-group').addClass('has-error'); // set error class to the control group
	            },

	            success: function (label) {
	                label.closest('.form-group').removeClass('has-error');
	                label.remove();
	            },

	            errorPlacement: function (error, element) {
	                error.insertAfter(element.closest('.input-icon'));
	            },

	            submitHandler: function (form) {
	                form.submit();
	            }
	        });

	        $('.forget-form input').keypress(function (e) {
	            if (e.which == 13) {
	                if ($('.forget-form').validate().form()) {
	                    $('.forget-form').submit();
	                }
	                return false;
	            }
	        });

	        jQuery('#forget-password').click(function () {
	            jQuery('.login-form').hide();
	            jQuery('.forget-form').show();
	        });

	        jQuery('#back-btn').click(function () {
	            jQuery('.login-form').show();
	            jQuery('.forget-form').hide();
	        });

	}

	var handleRegister = function () {
		/**
		 * 添加验证方法
		 * 以字母开头，5-17 字母、数字、下划线"_"
		 */
		// 验证真实姓名
		jQuery.validator.addMethod("realname", function(value, element) {   
		    var real = /^[\u4e00-\u9fa5]{2,4}$/;
		    return this.optional(element) || (real.test(value));
		}, "真实姓名只能填写2-4个汉字,不支持英文,数字和标点符号");

		// 验证用户名格式
		jQuery.validator.addMethod("username", function(value, element) {   
		    var user = /^[a-zA-Z][\w]{4,16}$/;
		    return this.optional(element) || (user.test(value));
		}, "以字母开头，5-17 字母、数字、下划线'_'");

		// 验证手机格式
		jQuery.validator.addMethod("tel", function(value, element) {   
		    var tel = /^0?(13[0-9]|15[012356789]|18[0236789])[0-9]{8}$/;
		    return this.optional(element) || (tel.test(value));
		}, "手机号码格式不正确");

		// 验证公司名称
		jQuery.validator.addMethod("company", function(value, element) {   
		    var company = /^([\u4e00-\u9fa5]|[a-zA-Z]){3,10}$/;
		    return this.optional(element) || (company.test(value));
		}, "只能输入汉字和字母");

        console.log(111);

        $('.register-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            ignore: "",
	            rules: {
	                realname: {
	                    required: true,
	                    realname: true
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
	                email: {
	                    required: true,
	                    email: true,
	                    remote : {
							url : checkEmail,
							type : 'post',
							dataType : 'json',
							data : {
								email : function () {
									return $('#email').val();
								}
							}
						}
	                },
	                comname: {
	                    required: true,
	                    company: true
	                },
	                username: {
	                    required: true,
	                    username: true,
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
	                password: {
	                    required: true,
	                    username: true
	                },
	                rpassword: {
	                    equalTo: "#register_password"
	                },
	                verify: {
	                	required: true
	                },
	                tnc: {
	                    required: true
	                }
	            },

	            messages: { // custom messages for radio buttons and checkboxes
	            	realname: {
	                    required: "真实姓名不能为空."
	                },
	                tel: {
	                    required: "联系方式不能为空.",
	                    remote: "该联系方式已经被注册."
	                },
	                email: {
	                    required: "邮箱不能为空.",
	                    email: "请输入正确格式的邮箱地址",
	                    remote: "该邮箱已经被注册."
	                },
	                comname: {
	                    required: "公司名称不能为空"
	                },
	                username: {
	                    required: "用户名不能为空",
	                    remote: "该用户名已经被注册."
	                },
	                password: {
	                    required: "密码不能为空"
	                },
	                rpassword: {
	                    equalTo: "两次输入密码不一致"
	                },
	                verify: {
	                	required: "验证码不能为空"
	                },
	                tnc: {
	                    required: "请先阅读并同意我们的协议条款."
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit   

	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.form-group').addClass('has-error'); // set error class to the control group
	            },

	            success: function (label) {
	                label.closest('.form-group').removeClass('has-error');
	                label.remove();
	            },

	            errorPlacement: function (error, element) {
	                if (element.attr("name") == "tnc") { // insert checkbox errors after the container                  
	                    error.insertAfter($('#register_tnc_error'));
	                } else if (element.closest('.input-icon').size() === 1) {
	                    error.insertAfter(element.closest('.input-icon'));
	                } else {
	                	error.insertAfter(element);
	                }
	            },

	            submitHandler: function (form) {
	                form.submit();
	            }
	        });
			
			$('.register1-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            ignore: "",
	            rules: {
	                realname: {
	                    required: true,
	                    realname: true
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
	                email: {
	                    required: true,
	                    email: true
	                },
	                comname: {
	                    required: true,
	                    company: true
	                },
	                username: {
	                    required: true,
	                    username: true,
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
	                password: {
	                    required: true,
	                    username: true
	                },
	                rpassword: {
	                    equalTo: "#register_password"
	                },
	                verify: {
	                	required: true
	                },
	                tnc: {
	                    required: true
	                }
	            },

	            messages: { // custom messages for radio buttons and checkboxes
	            	realname: {
	                    required: "真实姓名不能为空."
	                },
	                tel: {
	                    required: "联系方式不能为空.",
	                    remote: "该联系方式已经被注册."
	                },
	                comname: {
	                    required: "公司名称不能为空"
	                },
	                username: {
	                    required: "用户名不能为空",
	                    remote: "该用户名已经被注册."
	                },
	                password: {
	                    required: "密码不能为空"
	                },
	                rpassword: {
	                    equalTo: "两次输入密码不一致"
	                },
	                verify: {
	                	required: "验证码不能为空"
	                },
	                tnc: {
	                    required: "请先阅读并同意我们的协议条款."
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit   

	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.form-group').addClass('has-error'); // set error class to the control group
	            },

	            success: function (label) {
	                label.closest('.form-group').removeClass('has-error');
	                label.remove();
	            },

	            errorPlacement: function (error, element) {
	                if (element.attr("name") == "tnc") { // insert checkbox errors after the container                  
	                    error.insertAfter($('#register_tnc_error'));
	                } else if (element.closest('.input-icon').size() === 1) {
	                    error.insertAfter(element.closest('.input-icon'));
	                } else {
	                	error.insertAfter(element);
	                }
	            },

	            submitHandler: function (form) {
	                form.submit();
	            }
	        });

			$('.register-form input').keypress(function (e) {
	            if (e.which == 13) {
	                if ($('.register-form').validate().form()) {
	                    $('.register-form').submit();
	                }
	                return false;
	            }
	        });

	        jQuery('#register-btn').click(function () {
	            jQuery('.login-form').hide();
	            jQuery('.register-form').show();
	        });

	        jQuery('#register-back-btn').click(function () {
	            jQuery('.login-form').show();
	            jQuery('.register-form').hide();
	        });
	}
    
    return {
        //main function to initiate the module
        init: function () {
        	
            handleLogin();
            handleForgetPassword();
            handleRegister();    
        }

    };

}();