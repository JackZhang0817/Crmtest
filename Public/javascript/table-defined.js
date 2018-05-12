// javascript code
$(function(){
	//提交用户定制的显示字段
    $(function(){
        $("input:checkbox").click(function(){
            var str ='';    
            $('input:checked').each(function(){    
               str+=$(this).val()+',';                 
            });   
            $.post(displayFields_url, {tijiao:str}, function(msg){
                
            });
        });
    });

    //控制客户列表页面 字段显示
    $(function () {
        $("input:checkbox").each(function () {
            if (!$(this).is(":checked")) {
                var mark = $(this).attr("id")
                $("table ." + mark + "").hide();
            }
        });

        $("input:checkbox").change(function () {
            var mark = $(this).attr("id")
            if ($(this).is(":checked")) {
                $("table ." + mark + "").show();
            } else {
                $("table ." + mark + "").hide();
            }
        });
    });
});