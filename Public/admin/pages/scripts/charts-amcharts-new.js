var ChartsAmcharts = function() {
    var initChartSample6 = function(chart_data, chart_id) {
        console.log('内部' +chart_id)
        var chart = AmCharts.makeChart(chart_id, {
            "type": "pie",
            "theme": "light",
            "fontFamily": 'Open Sans',
            "color":    '#888',
            "dataProvider": chart_data,
            "valueField": "use_times",
            "titleField": "marterial_name",
            "exportConfig": {
                menuItems: [{
                    icon: Metronic.getGlobalPluginsPath() + "amcharts/amcharts/images/export.png",
                    format: 'png'
                }]
            }
        });
        $('#' + chart_id).closest('.portlet').find('.fullscreen').click(function() {
            chart.invalidateSize();
        });
    }
    return {
        //main function to initiate the module
        init: function(chart_data, chart_id) {
            console.log('id:' + chart_id)
            console.log(chart_data)
            initChartSample6(chart_data, chart_id);
        }
    };
}();