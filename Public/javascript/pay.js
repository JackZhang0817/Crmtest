//验证手机格式
jQuery.validator.addMethod("money", function(value, element) {
    var tel = /^[1-9][0-9]{3,}$/;
    //var tel = /^[0.1]$/;
    return this.optional(element) || (tel.test(value));
}, "请正确填写金额(整数且每次充值不能低于1000)");


$('.form-horizontal').validate({
    errorElement: 'span', //default input error message container
    errorClass: 'help-block', // default input error message class
    focusInvalid: false, // do not focus the last invalid input
    ignore: "",
    rules: {
        ordprice: {
            required: true,
            money: true
        }
    },

    messages: { // custom messages for radio buttons and checkboxes
        ordprice: {
            required: "请填写充值金额.",
            money: "请正确填写金额(整数且每次充值不能低于1000)"
        }
    }
});
