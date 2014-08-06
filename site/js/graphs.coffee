@Locale =
  ###
   # Client-side equivalent of {@link t()} function, but allows us to load locale strings
   # on the client directly.
  ###
  formatTemplate: (template, args) ->
    # load the template if it exists
    template = if LocaleStrings[template]? then LocaleStrings[template] else template

    for key, value of args
      template = template.replace(":" + key, value)
    return template

@Graphs =
  collection: {}

  ###
   # Forcibly re-render all the graphs on the page.
  ###
  rerenderAll: ->
    for key, graph of @collection
      graph.callback()

  render: (graph) ->
    throw new Error("No target set") unless graph.target
    console.log "rendering graph ", graph

    google.load("visualization", "1", {packages: ["corechart"]});

    graph.callback = ->
      ###
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
      ###

      url = "api/v1/graphs/" + graph.graph_type + "?days=" + graph.days +
            "&height=" + graph.height + "&width=" + graph.width +
            "&delta=" + graph.delta + "&arg0=" + graph.arg0 +
            "&arg0_resolved=" + graph.arg0_resolved + "&id=" + graph.id +
            "&no_technicals=" + graph.no_technicals

      queue_ajax_request url,
        dataType: 'json'
        success: (data, text, xhr) ->
          if not data.success? or !data.success
            Graphs.text graph,
              text: "Could not load graph data: Invalid response"
            throw new Error("Could not load graph data from " + url)
            return

          switch data.type
            when "linechart"
              Graphs.linechart graph, data
            else
              throw new Error("Could not render graph type " + data.type)

          console.log(data)
          window.setTimeout(graph.callback, 60000)

        error: (xhr, text, error) ->
          window.setTimeout(graph.callback, 60000)
          console.log if xhr.responseJSON? then xhr.responseJSON else xhr.responseText
          console.error error

          # try load the JSON anyway
          try
            console.log "trying to parse JSON"
            parsed = JSON.parse(xhr.responseText)
            Graphs.text graph,
              text: parsed.message
            return
          catch e
            console.log e

          Graphs.text graph,
            text: error

    # save this graphuration for later
    @collection[graph.target] = graph

    # create HTML elements as necessary, and reconfigure the DOM
    $(document).ready ->
      target = $("#" + graph.target)
      throw new Error("Could not find graph target " + graph.target) unless target.length > 0
      $(target[0]).width(graph.computedWidth)
      $(target[0]).height(graph.computedHeight)

      # create new elements
      throw new Error("Could not find #graph_contents_template to clone") unless $("#graph_contents_template").length > 0
      clone = $("#graph_contents_template").clone()
      $(clone).attr('id', '')
      $(clone).find(".graph-target").width(graph.graphWidth)
      $(clone).find(".graph-target").height(graph.graphHeight)

      $(target).append(clone)
      clone.show()

      # once the elements are ready, lets go
      graph.callback()

  ###
   # Render linecharts.
  ###
  linechart: (graph, result) ->
    target = $("#" + graph.target)
    throw new Error("No target " + graph.target + " found") unless target.length == 1
    target = target[0]

    table = new google.visualization.DataTable()
    series = []
    i = 0
    for column in result.columns
      if i > 0
        series.push
          lineWidth: 2
          color: @getChartColour(i)
      i++
      table.addColumn column.type, Locale.formatTemplate(column.title, column.args)

    formatted_data = []
    for key, value of result.data
      row = []
      row.push moment(key).toDate()
      for v in value
        row.push v
      formatted_data.push row
    table.addRows formatted_data

    options =
      legend:
        position: 'none'
      hAxis:
        gridlines:
          color: '#333'
        textStyle:
          color: 'white'
        format: 'd-MMM'
      vAxis:
        gridlines:
          color: '#333'
        textStyle:
          color: 'white'
      series: series
      chartArea:
        width: '90%'
        height: '85%'
        top: 20
        left: 60 # reduce padding
      backgroundColor: '#111'

    # draw the chart
    targetDiv = $(target).find(".graph-target")
    throw new Error("Could not find graph within " + target) unless targetDiv.length == 1
    chart = new google.visualization.LineChart(targetDiv[0])
    console.log table
    console.log "formatted data is ", formatted_data
    chart.draw(table, options)

    # also render subheadings
    heading = $(target).find(".graph_title a")
    heading.html(result.heading.label)
    heading.attr('href', result.heading.url)
    heading.attr('title', result.heading.title)

    # TODO outdated graph logic

    $(target).find(".subheading").html(result.subheading)
    $(target).find(".last-updated").html(result.lastUpdated)

  ###
   # Render simple text.
  ###
  text: (graph, result) ->
    target = $("#" + graph.target)
    throw new Error("No target " + graph.target + " found") unless target.length == 1
    target = target[0]

    throw new Error("No text defined in result") unless result.text?

    targetDiv = $(target).find(".graph-target .status_loading")
    throw new Error("Could not find graph within " + target) unless targetDiv.length == 1

    $(targetDiv).text(result.text)
    $(targetDiv).addClass('error-message')

  # TODO fill up with chart colours
  chartColours: ['#3366cc', '#dc3912']

  getChartColour: (i) ->
    throw new Error("Out of bounds colour") unless i <= @chartColours.length
    return @chartColours[i - 1]
