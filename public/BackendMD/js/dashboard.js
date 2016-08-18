$(function () {
    if ($(".select-search").length) {
        $('.select-search').select2();
    }
    getTodaystatistic();
    setInterval(function () {
        getTodaystatistic();
    }, 30000);
    // $.get(rootUrl + "backend/statistic/get-month-order", function (data) {
    //     chartStatistic(data, '#order-statistic-month');
    // });
    // $.get(rootUrl + "backend/statistic/get-month-sale", function (data) {
    //     chartStatistic(data, '#sales-statistic-month');
    // });
    $.get(rootUrl + "backend/statistic/get-month-user", function (data) {
        chartStatistic(data, '#customer-statistic-month');
    });
    // generateBarChart(".today-orders", 24, 50, true, "elastic", 1200, 50, "#00695C", "");
    // generateBarChart(".today-sales", 24, 50, true, "elastic", 1200, 50, "#EC407A", "");
    generateBarChart(".today-customers", 24, 50, true, "elastic", 1200, 50, "#29B6F6", "");
// Chart setup
    function progressCounter(element, radius, border, color, end, iconClass, textTitle, textAverage) {


        // Basic setup
        // ------------------------------

        // Main variables
        var d3Container = d3.select(element),
                startPercent = 0,
                iconSize = 32,
                endPercent = end,
                twoPi = Math.PI * 2,
                formatPercent = d3.format('.0%'),
                boxSize = radius * 2;

        // Values count
        var count = Math.abs((endPercent - startPercent) / 0.01);

        // Values step
        var step = endPercent < startPercent ? -0.01 : 0.01;



        // Create chart
        // ------------------------------

        // Add SVG element
        var container = d3Container.append('svg');

        // Add SVG group
        var svg = container
                .attr('width', boxSize)
                .attr('height', boxSize)
                .append('g')
                .attr('transform', 'translate(' + (boxSize / 2) + ',' + (boxSize / 2) + ')');



        // Construct chart layout
        // ------------------------------

        // Arc
        var arc = d3.svg.arc()
                .startAngle(0)
                .innerRadius(radius)
                .outerRadius(radius - border);



        //
        // Append chart elements
        //

        // Paths
        // ------------------------------

        // Background path
        svg.append('path')
                .attr('class', 'd3-progress-background')
                .attr('d', arc.endAngle(twoPi))
                .style('fill', '#eee');

        // Foreground path
        var foreground = svg.append('path')
                .attr('class', 'd3-progress-foreground')
                .attr('filter', 'url(#blur)')
                .style('fill', color)
                .style('stroke', color);

        // Front path
        var front = svg.append('path')
                .attr('class', 'd3-progress-front')
                .style('fill', color)
                .style('fill-opacity', 1);



        // Text
        // ------------------------------

        // Percentage text value
        var numberText = d3.select(element)
                .append('h2')
                .attr('class', 'mt-15 mb-5')

        // Icon
        d3.select(element)
                .append("i")
                .attr("class", iconClass + " counter-icon")
                .attr('style', 'top: ' + ((boxSize - iconSize) / 2) + 'px');

        // Title
        d3.select(element)
                .append('div')
                .text(textTitle);

        // Subtitle
        d3.select(element)
                .append('div')
                .attr('class', 'text-size-small text-muted')
                .text(textAverage);



        // Animation
        // ------------------------------

        // Animate path
        function updateProgress(progress) {
            foreground.attr('d', arc.endAngle(twoPi * progress));
            front.attr('d', arc.endAngle(twoPi * progress));
            numberText.text(formatPercent(progress));
        }

        // Animate text
        var progress = startPercent;
        (function loops() {
            updateProgress(progress);
            if (count > 0) {
                count--;
                progress += step;
                setTimeout(loops, 10);
            }
        })();
    }


    // Chart setup
    function generateBarChart(element, barQty, height, animate, easing, duration, delay, color, tooltip) {


        // Basic setup
        // ------------------------------

        // Add data set
        var bardata = [];
        for (var i = 0; i < barQty; i++) {
            bardata.push(Math.round(Math.random() * 10) + 10)
        }

        // Main variables
        var d3Container = d3.select(element),
                width = d3Container.node().getBoundingClientRect().width;



        // Construct scales
        // ------------------------------

        // Horizontal
        var x = d3.scale.ordinal()
                .rangeBands([0, width], 0.3)

        // Vertical
        var y = d3.scale.linear()
                .range([0, height]);



        // Set input domains
        // ------------------------------

        // Horizontal
        x.domain(d3.range(0, bardata.length))

        // Vertical
        y.domain([0, d3.max(bardata)])



        // Create chart
        // ------------------------------

        // Add svg element
        var container = d3Container.append('svg');

        // Add SVG group
        var svg = container
                .attr('width', width)
                .attr('height', height)
                .append('g');



        //
        // Append chart elements
        //

        // Bars
        var bars = svg.selectAll('rect')
                .data(bardata)
                .enter()
                .append('rect')
                .attr('class', 'd3-random-bars')
                .attr('width', x.rangeBand())
                .attr('x', function (d, i) {
                    return x(i);
                })
                .style('fill', color);



        // Tooltip
        // ------------------------------

        var tip = d3.tip()
                .attr('class', 'd3-tip')
                .offset([-10, 0]);

        // Show and hide
        if (tooltip == "hours" || tooltip == "goal" || tooltip == "members") {
            bars.call(tip)
                    .on('mouseover', tip.show)
                    .on('mouseout', tip.hide);
        }

        // Daily meetings tooltip content
        if (tooltip == "hours") {
            tip.html(function (d, i) {
                return "<div class='text-center'>" +
                        "<h6 class='no-margin'>" + d + "</h6>" +
                        "<span class='text-size-small'>meetings</span>" +
                        "<div class='text-size-small'>" + i + ":00" + "</div>" +
                        "</div>"
            });
        }

        // Statements tooltip content
        if (tooltip == "goal") {
            tip.html(function (d, i) {
                return "<div class='text-center'>" +
                        "<h6 class='no-margin'>" + d + "</h6>" +
                        "<span class='text-size-small'>statements</span>" +
                        "<div class='text-size-small'>" + i + ":00" + "</div>" +
                        "</div>"
            });
        }

        // Online members tooltip content
        if (tooltip == "members") {
            tip.html(function (d, i) {
                return "<div class='text-center'>" +
                        "<h6 class='no-margin'>" + d + "0" + "</h6>" +
                        "<span class='text-size-small'>members</span>" +
                        "<div class='text-size-small'>" + i + ":00" + "</div>" +
                        "</div>"
            });
        }



        // Bar loading animation
        // ------------------------------

        // Choose between animated or static
        if (animate) {
            withAnimation();
        } else {
            withoutAnimation();
        }

        // Animate on load
        function withAnimation() {
            bars
                    .attr('height', 0)
                    .attr('y', height)
                    .transition()
                    .attr('height', function (d) {
                        return y(d);
                    })
                    .attr('y', function (d) {
                        return height - y(d);
                    })
                    .delay(function (d, i) {
                        return i * delay;
                    })
                    .duration(duration)
                    .ease(easing);
        }

        // Load without animateion
        function withoutAnimation() {
            bars
                    .attr('height', function (d) {
                        return y(d);
                    })
                    .attr('y', function (d) {
                        return height - y(d);
                    })
        }



        // Resize chart
        // ------------------------------

        // Call function on window resize
        $(window).on('resize', barsResize);

        // Call function on sidebar width change
        $('.sidebar-control').on('click', barsResize);

        // Resize function
        // 
        // Since D3 doesn't support SVG resize by default,
        // we need to manually specify parts of the graph that need to 
        // be updated on window resize
        function barsResize() {

            // Layout variables
            width = d3Container.node().getBoundingClientRect().width;


            // Layout
            // -------------------------

            // Main svg width
            container.attr("width", width);

            // Width of appended group
            svg.attr("width", width);

            // Horizontal range
            x.rangeBands([0, width], 0.3);


            // Chart elements
            // -------------------------

            // Bars
            svg.selectAll('.d3-random-bars')
                    .attr('width', x.rangeBand())
                    .attr('x', function (d, i) {
                        return x(i);
                    });
        }

    }

});
function getTodaystatistic() {
    $.ajax({
        type: "POST",
        url: rootUrl + "backend/statistic/get-today-statistic",
        success: function (response) {
            var result = $.parseJSON(response);
            if (result)
            {
                var comma_separator_number_step = $.animateNumber.numberStepFactories.separator(',');
                // $(".nu-today-order").animateNumber({number: result.order, numberStep: comma_separator_number_step, easing: 'easeInQuad'}, 2000);
                // $(".nu-today-order-new").animateNumber({number: result.order_new, numberStep: comma_separator_number_step, easing: 'easeInQuad'}, 2000);
                // $(".nu-today-sale").animateNumber({number: result.total_sale, numberStep: comma_separator_number_step, easing: 'easeInQuad'}, 2000);
                $(".nu-today-customers").animateNumber({number: result.customer, numberStep: comma_separator_number_step, easing: 'easeInQuad'}, 2000);

            } else {
                // $(".nu-today-order").animateNumber({number: 0, easing: 'easeInQuad'}, 2000);
                // $(".nu-today-order-new").animateNumber({number: 0, easing: 'easeInQuad'}, 2000);
                // $(".nu-today-sale").animateNumber({number: 0, easing: 'easeInQuad'}, 2000);
                $(".nu-today-customers").animateNumber({number: 0, easing: 'easeInQuad'}, 2000);
            }
        }
        ,
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });
}
function chartStatistic(data, elementID) {
    var data = $.parseJSON(data);
    $(elementID).highcharts({
        title: {
            text: data.title,
            x: -20 //center
        },
        xAxis: {
            categories: data.xAxis
        },
        yAxis: {
            title: {
                text: 'Statistic'
            },
            plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
        },
        tooltip: {
            crosshairs: true,
            shared: true
        },
        legend: {
            align: 'center',
            verticalAlign: 'bottom',
            borderWidth: 0
        },
        series: data.data
    });
}
function filterStatistic(form, link, id) {
    var data = $("." + form).serialize();
    $.ajax({
        type: "POST",
        url: rootUrl + "backend/statistic/" + link,
        data: data,
        success: function (response) {
            if (response)
            {
                chartStatistic(response, '#' + id);

            }
        }
        ,
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });
}