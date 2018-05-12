var TableAdvanced = function () {

    var initTable1 = function() {
        /*
         * Insert a 'details' column to the table
         */
        var nCloneTh = document.createElement( 'th' );
        var nCloneTd = document.createElement( 'td' );
        nCloneTd.innerHTML = '<span class="row-details row-details-close"></span>';
         
        $('.sample_1 thead tr').each( function () {
            this.insertBefore( nCloneTh, this.childNodes[0] );
        } );
         
        $('.sample_1 tbody tr').each( function () {
            this.insertBefore(  nCloneTd.cloneNode( true ), this.childNodes[0] );
        } );
         
        /* Add event listener for opening and closing details
         * Note that the indicator for showing which row is open is not controlled by DataTables,
         * rather it is done here
         */
        $('.sample_1').on('click', ' tbody td .row-details', function () {
            var nTr = $(this).parents('tr')[0];
            if ( oTable.fnIsOpen(nTr) )
            {
                /* This row is already open - close it */
                $(this).addClass("row-details-close").removeClass("row-details-open");
                oTable.fnClose( nTr );
            }
            else
            {
                /* Open this row */                
                $(this).addClass("row-details-open").removeClass("row-details-close");
                oTable.fnOpen( nTr, fnFormatDetails(oTable, nTr), 'details' );
            }
        });
    }

    var initTable2 = function() {
		//Jquery获取当前网页的URL   
		var url = window.location.pathname;		//设置或获取对象指定的文件名或路径
		var urlarr = new Array();	//定义一个数组,用来拆分URL
		urlarr = url.split("/");	//根据 / 拆分URL
		
		//根据数组的第N个元素 判断当前页面属于哪一个栏目  根据URL规律  前三个元素固定, 只有第四个元素是变化的 所以直接判断第四个元素即可
		if(urlarr[2] == 'Customer'){
			if(urlarr[3] == 'htCustomer'){
				url = 10;
			}else if(urlarr[3] == 'ddCustomer'){
				url = 8;
			}else if(urlarr[3] == 'qdCustomer'){
				url = 9;
			}else{
				url = 7;
			}
		}else if(urlarr[1] == 'Customer'){
			if(urlarr[2] == 'htCustomer'){
				url = 10;
			}else if(urlarr[2] == 'ddCustomer'){
				url = 8;
			}else if(urlarr[2] == 'qdCustomer'){
				url = 9;
			}else{
				url = 7;
			}
		}else{
			if(urlarr[3] == 'xhtCustomer' || urlarr[3] == 'zhtCustomer'){
				url = 10;
			}else{
				url = 9;
			}
		}
		
        var oTable = $('#sample_2').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ]
        });
        
		//提交用户定制的显示字段
		$(function(){
			$("input:checkbox").click(function(){
				var str ='';    
				$('input:checked').each(function(){    
				   str+=$(this).val()+',';				   
				});    
				$.post("/Admin/Customer/tijiao",{tijiao:str},function(msg){
				
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
    }

    return {

        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }

            initTable1();
            initTable2();
        }

    };

}();