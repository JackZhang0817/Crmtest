//点击刷新验证码
var verifyUrl = $('.verify').attr('src');
$('.verify').click(function () {
    $(this).attr('src', verifyUrl + '?' + Math.random());
});

/**
 * 添加验证方法
 * 以字母开头，5-17 字母、数字、下划线"_"
 */
    // 验证真实姓名
//jQuery.validator.addMethod("realname", function(value, element) {
//    var real = /^[\u4e00-\u9fa5]{2,4}$/;
//    return this.optional(element) || (real.test(value));
//}, "真实姓名只能填写2-4个汉字,不支持英文,数字和标点符号");

// 验证用户名格式
jQuery.validator.addMethod("username", function(value, element) {
    var user = /^[a-zA-Z][\w]{4,16}$/;
    return this.optional(element) || (user.test(value));
}, "以字母开头，数字相结合, 可以使用下划线'_', 长度为5-17位");

// 验证手机格式
jQuery.validator.addMethod("tel", function(value, element) {
    var tel = /^0?(13[0-9]|15[012356789]|18[0-9]|17[0678]|14[57])[0-9]{8}$/;
    return this.optional(element) || (tel.test(value));
}, "手机号码格式不正确");

// 验证公司名称
jQuery.validator.addMethod("company", function(value, element) {
    var company = /^([\u4e00-\u9fa5]|[a-zA-Z]){1,50}$/;
    return this.optional(element) || (company.test(value));
}, "只能输入汉字和字母");

$('.register-form').validate({
    errorElement: 'span', //default input error message container
    errorClass: 'help-block', // default input error message class
    focusInvalid: false, // do not focus the last invalid input
    ignore: "",
    rules: {
        realname: {
            required: true,
            company: true
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
        }
    }
});
