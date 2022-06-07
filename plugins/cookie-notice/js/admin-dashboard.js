(function ($) {

    // ready event
    $(function () {

        var charts = cnDashboardArgs.charts;
        // console.log(charts);

        if (Object.entries(charts).length > 0) {
            for (const [key, config] of Object.entries(charts)) {

                var canvas = document.getElementById('cn-' + key + '-chart');
                // console.log(canvas);

                // options per chart type
                var options = {
                    doughnut: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },
                        hover: {
                            mode: 'label'
                        },
                        layout: {
                            padding: 0
                        }
                    },
                    line: {
                        scales: {
                            xAxes: [{
                                    afterTickToLabelConversion: function (data) {


                                        var xLabels = data.ticks;

                                        xLabels.forEach(function (labels, i) {
                                            if (i % 2 == 1) {
                                                xLabels[i] = '';
                                            }
                                        });
                                    }
                                }]
                        }
                    }
                }

                // console.log(config);

                if (canvas) {
                    config.options = options.hasOwnProperty(config.type) ? options[config.type] : {};

                    var chart = new Chart(canvas, config);

                    // console.log(config);
                    // console.log(chart);

                    chart.update();
                }

            }
        }
    });

})(jQuery);