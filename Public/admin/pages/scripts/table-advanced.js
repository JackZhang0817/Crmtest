var TableAdvanced = function () {

    var initTable1 = function () {
        var table = $('.sample_1');

        /* Set tabletools buttons and button container */

        var oTable = table.dataTable({
            "order": [
                [0, 'asc']
            ]
        });
    }

    return {

        //main function to initiate the module
        init: function () {

            if (!jQuery().dataTable) {
                return;
            }

            initTable1();
        }

    };

}();