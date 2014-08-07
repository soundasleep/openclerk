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

    try
      element = $("#" + graph.target)
      throw new Error("No target " + graph.target + " found") unless element.length == 1
      graph.element = element[0]

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

        if not graph.days?
          Graphs.text graph.element, graph,
            text: "No days specified"
          return

        url = "api/v1/graphs/" + graph.graph_type + "?days=" + graph.days
        if graph.delta?
          url += "&delta=" + graph.delta
        if graph.arg0?
          url += "&arg0=" + graph.arg0
        if graph.arg0_resolved?
          url += "&arg0_resolved=" + graph.arg0_resolved
        if graph.technical_type?
          url += "&technical=" + graph.technical_type + "&technical_period=" + graph.technical_period
        if graph.user_id?
          url += "&user_id=" + graph.user_id + "&user_hash=" + graph.user_hash

        queue_ajax_request url,
          dataType: 'json'
          success: (data, text, xhr) ->
            try
              if not data.success? or !data.success
                throw new Error("Could not load graph data: Invalid response")

              switch data.type
                when "linechart"
                  Graphs.linechart graph.element, graph, data
                when "vertical"
                  Graphs.vertical graph.element, graph, data
                else
                  throw new Error("Could not render graph type " + data.type)
            catch error
              Graphs.text graph.element, graph,
                text: error.message
              console.error error

            window.setTimeout(graph.callback, 60000)

          error: (xhr, text, error) ->
            window.setTimeout(graph.callback, 60000)
            console.log if xhr.responseJSON? then xhr.responseJSON else xhr.responseText
            console.error error

            # try load the JSON anyway
            try
              parsed = JSON.parse(xhr.responseText)
              Graphs.text graph.element, graph,
                text: parsed.message
              return
            catch e
              console.log e

            Graphs.text graph.element, graph,
              text: error.message

      # save this graphuration for later
      @collection[graph.target] = graph

      # create HTML elements as necessary, and reconfigure the DOM
      $(document).ready =>
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

        # let users refresh the graph manually by clicking on the last-updated link
        $(target).find(".last-updated").click =>
          graph.callback()

        # once the elements are ready, lets go
        graph.ready = true
        graph.callback()

    catch error
      console.error error

  ###
   # Render linecharts.
  ###
  linechart: (target, graph, result) ->
    throw new Error("Graph has not been initialised") unless graph.ready?
    throw new Error("Data has no columns") unless result.columns?
    throw new Error("Data has no key") unless result.key?

    table = new google.visualization.DataTable()

    # add the key column as a column for the DataTable
    column = result.key
    table.addColumn column.type, Locale.formatTemplate(column.title, column.args)

    series = []
    vAxes = []
    i = 0
    for column in result.columns
      column.lineWidth = 2 if not column.lineWidth?
      type = column.type
      if column.technical
        type = "number"
        column.lineWidth = 1
      if column.type == "percent"
        type = "number"

      series.push
        lineWidth: column.lineWidth
        color: @getChartColour(i)
      i++
      table.addColumn type, Locale.formatTemplate(column.title, column.args)

      # set up vertical axes as necessary
      if column.min? and column.max?
        vAxes.push
          minValue: column.min
          maxValue: column.max

    formatted_data = []
    for key, value of result.data
      if value.length != result.columns.length
        console.log "row: ", value, " columns: ", result.columns
        throw new Error("Row '" + key + "' did not have exactly " + result.columns.length + " columns")
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
        width: '80%'
        height: '75%'
        top: 20
        left: Math.min(60, 30 * graph.width) # reduce padding
      backgroundColor: '#111'
      vAxes: vAxes

    if graph.width >= 8
      options.chartArea.width = '90%'
      options.chartArea.height = '85%'

    # draw the chart
    targetDiv = $(target).find(".graph-target")
    throw new Error("Could not find graph within " + target) unless targetDiv.length == 1
    chart = new google.visualization.LineChart(targetDiv[0])
    chart.draw(table, options)

    Graphs.renderHeadings target, graph, result

  ###
   # Render a vertical graph.
  ###
  vertical: (target, graph, result) ->
    throw new Error("Graph has not been initialised") unless graph.ready?
    throw new Error("Data has no columns") unless result.columns?
    throw new Error("Data has no key") unless result.key?
    console.log "rendering ", result

    # create new elements
    throw new Error("Could not find #graph_table_template to clone") unless $("#graph_table_template").length > 0
    clone = $("#graph_table_template").clone()
    $(clone).attr('id', '')
    # $(clone).find(".graph-target").width(graph.graphWidth)
    # $(clone).find(".graph-target").height(graph.graphHeight)

    thead = document.createElement('thead')
    tr = document.createElement('tr')
    for column in result.columns
      th = document.createElement('th')
      $(th).html(Locale.formatTemplate(column.title, column.args))
      $(th).addClass(column.type)
      $(tr).append(th)

    $(thead).append(tr)
    $(clone).find('table').append(thead)

    tbody = document.createElement('tbody')
    for key, value of result.data
      tr = document.createElement('tr')
      i = 0
      for v in value
        td = document.createElement(if result.columns[i].heading? then 'th' else 'td')
        $(td).html(v)
        $(td).addClass(result.columns[i].type)
        $(tr).append(td)
        i++

      $(tbody).append(tr)

    $(clone).find('table').append(tbody)

    $(target).find(".status_loading").remove()

    $(target).find(".graph-target").empty()
    $(target).find(".graph-target").append(clone)
    clone.show()

    Graphs.renderHeadings target, graph, result

  ###
   # Render simple text.
  ###
  text: (target, graph, result) ->
    throw new Error("No text defined in result") unless result.text?
    throw new Error("Graph has not been initialised") unless graph.ready?

    targetDiv = $(target).find(".graph-target .status_loading")
    throw new Error("Could not find graph within " + target) unless targetDiv.length == 1

    $(targetDiv).text(result.text)
    $(targetDiv).addClass('error-message')

  renderHeadings: (target, graph, result) ->
    # add classses, if provided
    if result.classes
      $(target).addClass(result.classes)

    # also render subheadings
    heading = $(target).find(".graph_title a")
    heading.html(result.heading.label)
    heading.attr('href', result.heading.url)
    heading.attr('title', result.heading.title)

    # TODO outdated graph logic

    if result.subheading
      $(target).find(".subheading").html(result.subheading)
    else
      $(target).find(".subheading").hide()

    if result.lastUpdated
      $(target).find(".last-updated").html(result.lastUpdated)
    else
      $(target).find(".last-updated").hide()

    if result.h1
      $(target).find(".h1").html(result.h1)
    else
      $(target).find(".h1").hide()

    if result.h2
      $(target).find(".h2").html(result.h2)
    else
      $(target).find(".h2").hide()

    $(target).find(".admin-stats").html(result.time + " ms")

  chartColours: [
    "#3366cc",
    "#dc3912",
    "#ff9900",
    "#109618",
    "#990099",
    "#3b3eac",
    "#0099c6",
    "#dd4477",
    "#66aa00",
    "#b82e2e",
    "#316395",
    "#994499",
    "#22aa99",
    "#aaaa11",
    "#6633cc",
    "#e67300",
    "#8b0707",
    "#329262",
    "#5574a6",
    "#3b3eac",
  ]

  getChartColour: (i) ->
    return @chartColours[i % @chartColours.length]
