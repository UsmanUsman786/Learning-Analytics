$(function() {
    $.ajax({

        url: 'http://localhost:8080/server/servercontroller?REQUEST_TYPE=weakTopic_report',
        type: 'GET',
        success: function(data) {
            chartData = data;
            var chartProperties = {
                "caption": "Assessment vise marks obtained (little variation of Topic vise no. of students) report",
                "xAxisName": "assessmentID",
                "yAxisName": "marksObtained",
                "rotatevalues": "1",
                "theme": "zune"
            };

            apiChart = new FusionCharts({
                type: 'column2d',
                renderAt: 'chart-container',
                width: '550',
                height: '350',
                dataFormat: 'json',
                dataSource: {
                    "chart": chartProperties,
                    "data": chartData
                }
            });
            apiChart.render();
        }
    });
});