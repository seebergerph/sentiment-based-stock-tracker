class StockChart {

    constructor(id, margin, width, height, intraday, data) {
        this.id = id;
        this.margin = margin;
        this.width = width;
        this.height = height;
        this.intraday = intraday;
        this.data = data;
        this.xScale;
        this.yScale;
        this.initialiseChart(this.prepareData(this.data));
    }

    prepareData(data) {
        var prepared_data = []
        for (var i = 0; i < data.length; i++) { 
            prepared_data.push({'Date': (this.intraday? d3.timeParse("%Y-%m-%d %H:%M:%S")(data[i]['datetime']) : d3.timeParse("%Y-%m-%d")(data[i]['datetime'])),
                'Open': parseFloat(data[i]['open']),
                'High': parseFloat(data[i]['high']),
                'Low': parseFloat(data[i]['low']),
                'Close': parseFloat(data[i]['close']),
                'Volume': parseInt(data[i]['volume'])});
        }

        // Ascending order necessary for d3.bisect
        prepared_data.sort(function compare(a, b) {
            var dateA = new Date(a.Date);
            var dateB = new Date(b.Date);
            return dateA - dateB;
        });
        return prepared_data;
    }

    initialiseChart(data) {
        const width = this.width;
        const height = this.height;

        // Append the svg object to the body of the page
        const svg = d3.select("#chart")
        .append("svg")
            .attr("width", width + this.margin.left + this.margin.right)
            .attr("height", height + this.margin.top + this.margin.bottom)
            .call(responsivefy)
        .append("g")
            .attr("transform",
                  "translate(" + this.margin.left + "," + this.margin.top + ")");


        // Create X and Y axis
        this.xScale = d3.scaleTime()
            .domain(d3.extent(data, function(d) { return d.Date; }))
            .range([0, width]);

        const yMin = d3.min(data, d => { return d.Close; });
        const yMax = d3.max(data, d => { return d.Close; });

        this.yScale = d3.scaleLinear()
            .domain([yMin - (yMin*0.1), yMax])
            .range([height, 0]);

        const xScale = this.xScale;
        const yScale = this.yScale;
        
        // Add X and Y axis
        svg.append("g")
            .attr("id", "xAxis")
            .attr("transform", `translate(0,${height})`)
            .call(d3.axisBottom(xScale));

        svg.append("g")
            .attr("id", "yAxis")
            .attr("transform", `translate(${width}, 0)`)
            .call(d3.axisRight(yScale));


        // Add the line
        svg.append("path")
            .datum(data)
            .attr("id", "stockChart")
            .attr("fill", "none")
            .attr("stroke", "steelblue")
            .attr("stroke-width", 1.5)
            .attr("d", d3.line()
                .x(function(d) { return xScale(d.Date) })
                .y(function(d) { return yScale(d.Close) })
            );


        // Add volume series bars
        const volumeData = this.createVolumeData(data);

        const yMinVolume = d3.min(volumeData, d => { return Math.min(d.Volume); });
        const yMaxVolume = d3.max(volumeData, d => { return Math.max(d.Volume);});
    
        const yVolumeScale = d3.scaleLinear()
            .domain([yMinVolume, yMaxVolume])
            .range([height, height*0.8]);      
            
        svg.selectAll()
            .data(volumeData)
            .enter()
            .append('rect')
            .attr('x', d => {
                return xScale(d.Date);
            })
            .attr('y', d => {
                return yVolumeScale(d.Volume);
            })
            .attr('fill', (d, i) => {
                if (i === 0) {
                    return '#03a678';
                } else {
                    return volumeData[i - 1].Close > d.Close ? '#c0392b' : '#03a678';
                }
            })
            .attr('width', 1.5)
            .attr('height', d => {
                return height - yVolumeScale(d.Volume);
            });


        // Renders x and y crosshair
        const focus = svg.append('g')
            .attr('class', 'focus')
            .style('display', 'none');

        focus.append('circle').attr('r', 4.5);
        focus.append('line').classed('x', true);
        focus.append('line').classed('y', true);

        svg.append('rect')
            .attr('class', 'overlay')
            .attr('width', width)
            .attr('height', height)
            .on('mouseover', () => focus.style('display', null))
            .on('mouseout', () => focus.style('display', 'none'))
            .on('mousemove', generateCrosshair);

        d3.select('.overlay').style('fill', 'none');
        d3.select('.overlay').style('pointer-events', 'all');
        d3.selectAll('.focus line').style('fill', 'none');
        d3.selectAll('.focus line').style('stroke', '#67809f');
        d3.selectAll('.focus line').style('stroke-width', '1.5px');
        d3.selectAll('.focus line').style('stroke-dasharray', '3 3');

        const bisectDate = d3.bisector(d => d.Date).left;
        function generateCrosshair() {
            // Returns corresponding value from the domain
            var correspondingDate = xScale.invert(d3.mouse(this)[0]);

            // Gets insertion point
            var i = bisectDate(data, correspondingDate, 1);
            var d0 = data[i - 1];
            var d1 = data[i];
            var currentPoint = correspondingDate - d0.Date > d1.Date - correspondingDate ? d1 : d0;
  
            focus.attr('transform',`translate(${xScale(currentPoint.Date)}, ${yScale(currentPoint.Close)})`);

            focus.select('line.x')
                .attr('x1', 0)
                .attr('x2', width - xScale(currentPoint.Date))
                .attr('y1', 0)
                .attr('y2', 0);

            focus.select('line.y')
                .attr('x1', 0)
                .attr('x2', 0)
                .attr('y1', 0)
                .attr('y2', height - yScale(currentPoint.Close));

            updateLegends(currentPoint);
        }

        const updateLegends = currentData => {
            d3.selectAll('.lineLegend').remove();
            const legendKeys = Object.keys(data[0]);
            const lineLegend = svg
                .selectAll('.lineLegend')
                .data(legendKeys)
                .enter()
                .append('g')
                .attr('class', 'lineLegend')
                .attr('transform', (d, i) => { return `translate(0, ${i * 15})`; });

            lineLegend
                .append('text')
                .text(d => {
                    if (d === 'Date') {
                      return `${d}: ` + (this.intraday? currentData[d].toLocaleString().replace(/(.*)\D\d+/, '$1') : currentData[d].toLocaleDateString());
                    } else if (
                      d === 'High' ||
                      d === 'Low' ||
                      d === 'Open' ||
                      d === 'Close'
                    ) {
                      return `${d}: ` + parseFloat(currentData[d]).toFixed(2);
                    } else {
                      return `${d}: ${currentData[d]}`;
                    }
                  })
                .style('font-size', '0.7em')
                .style('fill', 'black')
                .attr('transform', 'translate(0,9)');
            }  
    }

    createVolumeData(data) {
        return data.filter(d => d.Volume !== null && d.Volume !== 0);
    }
}