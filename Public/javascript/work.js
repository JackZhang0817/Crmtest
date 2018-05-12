// 验证手机格式
jQuery.validator.addMethod("tel", function(value, element) {
    var tel = /^[0-9]*$/;
    return this.optional(element) || (tel.test(value));
}, "只能输入数字");

jQuery.validator.addMethod("num", function(value, element) {
    var num = /^\d{0,8}\.{0,1}(\d{1,2})$/;
    return this.optional(element) || (num.test(value));
}, "只能输入数字");

jQuery.validator.addMethod("date", function(value, element) {
    var date = /^(\d{4})-(\d{2})-(\d{2})$/;
    return this.optional(element) || (date.test(value));
}, "只能输入 2015-06-14 格式的日期");

$('.form-horizontal').validate({
    errorElement: 'span', //default input error message container
    errorClass: 'help-block', // default input error message class
    focusInvalid: false, // do not focus the last invalid input
    ignore: "",
    rules: {
        Userid: {
            required: true
        },
        CName: {
            required: true
        },
        Tel: {
            required: true,
            tel: true
        }
    },

    messages: { // custom messages for radio buttons and checkboxes
        Userid: {
            required: "请选择业务员."
        },
        Company: {
            required: "客户姓名不能为空."
        },
        Contact: {
            required: "客户姓名不能为空."
        },        
        Tel: {
            required: "联系方式不能为空.",
            tel:"只能输入数字"
        }
    }
});
