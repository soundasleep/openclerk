(function() {
  this.Graphs = {
    collection: {},

    /*
      * Forcibly re-render all the graphs on the page.
     */
    rerenderAll: function() {
      var graph, key, _ref, _results;
      _ref = this.collection;
      _results = [];
      for (key in _ref) {
        graph = _ref[key];
        _results.push(graph.callback());
      }
      return _results;
    },
    render: function(graph) {
      if (!graph.target) {
        throw new Error("No target set");
      }
      console.log("rendering graph ", graph);
      google.load("visualization", "1", {
        packages: ["corechart"]
      });
      graph.callback = function() {

        /*
        url = "graph_public?graph_type=" + graph.graph_type + "&days=" + graph.days +
              "&height=" + graph.height + "&width=" + graph.width +
              "&delta=" + graph.delta + "&arg0=" + graph.arg0 +
              "&arg0_resolved=" + graph.arg0_resolved + "&id=" + graph.id +
              "&no_technicals=" + graph.no_technicals
        
        queue_ajax_request url,
          success: (data, text, xhr) ->
            $("#" + graph.target).html(data)
            window.setTimeout(graph.callback, 60000)
        
          error: (xhr, text, error) ->
            $("#" + graph.target).html(xhr.responseText)
            window.setTimeout(graph.callback, 60000)
         */
        var url;
        url = "api/v1/graphs/" + graph.graph_type + "?days=" + graph.days + "&height=" + graph.height + "&width=" + graph.width + "&delta=" + graph.delta + "&arg0=" + graph.arg0 + "&arg0_resolved=" + graph.arg0_resolved + "&id=" + graph.id + "&no_technicals=" + graph.no_technicals;
        return queue_ajax_request(url, {
          success: function(data, text, xhr) {
            switch (data.type) {
              case "linechart":
                Graphs.linechart(graph, data);
                break;
              default:
                throw new Error("Could not render graph type " + data.type);
            }
            return console.log(data);
          },
          error: function(xhr, text, error) {
            console.log(xhr.responseJSON != null ? xhr.responseJson : xhr.responseText);
            return console.error(error);
          }
        });
      };
      this.collection[graph.target] = graph;
      return $(document).ready(function() {
        var clone, target;
        target = $("#" + graph.target);
        if (!(target.length > 0)) {
          throw new Error("Could not find graph target " + graph.target);
        }
        $(target[0]).width(graph.computedWidth);
        $(target[0]).height(graph.computedHeight);
        if (!($("#graph_contents_template").length > 0)) {
          throw new Error("Could not find #graph_contents_template to clone");
        }
        clone = $("#graph_contents_template").clone();
        $(clone).attr('id', '');
        $(clone).find(".graph-target").width(graph.graphWidth);
        $(clone).find(".graph-target").height(graph.graphHeight);
        $(target).append(clone);
        clone.show();
        return graph.callback();
      });
    },
    linechart: function(graph, result) {
      var chart, column, formatted_data, heading, i, key, options, row, series, table, target, targetDiv, v, value, _i, _j, _len, _len1, _ref, _ref1;
      target = $("#" + graph.target);
      if (target.length !== 1) {
        throw new Error("No target " + graph.target + " found");
      }
      target = target[0];
      table = new google.visualization.DataTable();
      series = [];
      i = 0;
      _ref = result.columns;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        column = _ref[_i];
        if (i > 0) {
          series.push({
            lineWidth: 2,
            color: this.getChartColour(i)
          });
        }
        i++;
        table.addColumn(column.type, column.title);
      }
      formatted_data = [];
      _ref1 = result.data;
      for (key in _ref1) {
        value = _ref1[key];
        row = [];
        row.push(moment(key).toDate());
        for (_j = 0, _len1 = value.length; _j < _len1; _j++) {
          v = value[_j];
          row.push(v);
        }
        formatted_data.push(row);
      }
      table.addRows(formatted_data);
      options = {
        legend: {
          position: 'none'
        },
        hAxis: {
          gridlines: {
            color: '#333'
          },
          textStyle: {
            color: 'white'
          },
          format: 'd-MMM'
        },
        vAxis: {
          gridlines: {
            color: '#333'
          },
          textStyle: {
            color: 'white'
          }
        },
        series: series,
        chartArea: {
          width: '90%',
          height: '85%',
          top: 20,
          left: 60
        },
        backgroundColor: '#111'
      };
      targetDiv = $(target).find(".graph-target");
      if (targetDiv.length !== 1) {
        throw new Error("Could not find graph within " + target);
      }
      chart = new google.visualization.LineChart(targetDiv[0]);
      console.log(table);
      console.log("formatted data is ", formatted_data);
      chart.draw(table, options);
      heading = $(target).find(".graph_title a");
      heading.html(result.heading.label);
      heading.attr('href', result.heading.url);
      heading.attr('title', result.heading.title);
      $(target).find(".subheading").html(result.subheading);
      return $(target).find(".last-updated").html(result.lastUpdated);
    },
    chartColours: ['#3366cc', '#dc3912'],
    getChartColour: function(i) {
      if (!(i <= this.chartColours.length)) {
        throw new Error("Out of bounds colour");
      }
      return this.chartColours[i - 1];
    }
  };

}).call(this);
