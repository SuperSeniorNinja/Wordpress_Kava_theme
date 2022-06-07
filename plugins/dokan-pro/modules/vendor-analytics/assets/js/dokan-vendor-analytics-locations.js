var data = [];
var max = 0;

for ( country in dokanVendorAnalytics.chart_data ) {
    var users = dokanVendorAnalytics.chart_data[ country ];

    max = users > max ? users : max;

    data.push( {
        name: country,
        value: users
    } );
}

var location_analytics = echarts.init( document.getElementById('dokan-vendor-analytics-location-map') );

var option = {
    tooltip: {
        trigger: 'item',
        backgroundColor: '#ffffff',
        borderWidth: 1,
        borderColor: '#eaeaea',
        textStyle: {
            color: '#444444',
            fontSize: 12,
        },
        formatter: function (params) {
            if ( ! params.name ) {
                return null;
            }

            return '<strong>' + params.name + '</strong><br /> Users: <strong>' + params.value + '</strong>';
        },
    },
    visualMap: {
        min: 0,
        max: max,
        text:['High','Low'],
        realtime: true,
        calculable: true,
        inRange: {
            color: ['#eeeeee', '#9be7ff', '#002f6c']
        }
    },
    series: [
        {
            type: 'map',
            mapType: 'world',
            roam: false,
            data: data,
            emphasis: {
                label: {
                    show: false,
                }
            },
            itemStyle: {
                areaColor: '#f3f3f3',
                borderColor: '#cacaca',
            }
        }
    ]
};

location_analytics.setOption(option, true);
