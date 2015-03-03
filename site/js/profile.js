/**
 * Add graph functionality.
 */
$(document).ready(function() {
  if (typeof graph_types == 'undefined') {
    // we can't add graph types to the page if they're not defined
    return;
  }

  var i;
  var e = $(document).find("#graph_category"), template = $(document).find("#graph_category_template");
  var first_element = -1;
  template.hide();
  // first we load all categories and subcategories
  for (i = 0; i < graph_types().length; i++) {
    var graph_type = graph_types()[i];
    if (typeof graph_type['category'] != 'undefined' || typeof graph_type['subcategory'] != 'undefined') {
      var temp = template.clone();
      if (typeof graph_type['category'] != 'undefined') {
        temp.text(graph_type['category']);
        temp.attr('disabled', true);
        temp.addClass('category');
        temp.addClass('category_' + graph_type['id']);
      } else if (typeof graph_type['subcategory'] != 'undefined') {
        // don't add this subcategory if this subcategory has no visible items in it
        var has_visible = false;
        for (var j = i + 1; j < graph_types().length; j++) {
          if (typeof graph_types()[j]['category'] != 'undefined' || typeof graph_types()[j]['subcategory'] != 'undefined') {
            break;
          }
          if (typeof graph_types()[j]['hide'] == 'undefined' || !graph_types()[j]['hide']) {
            has_visible = true;
            break;
          }
        }
        if (!has_visible) continue;

        var temp = template.clone();
        temp.attr('value', i);
        temp.text(graph_type['subcategory']);
        if (first_element < 0) {
          first_element = i;
        }
      }
      temp.data('index', i);
      temp.attr('id', '');
      e.append(temp);
      temp.show();
    }
  }
  var callback = function(event) {
    var e = $(document).find("#graph_type"), template = $(document).find("#graph_category_template");
    $(e).find("option").not("#graph_category_template").remove();

    // and then we load graph types, depending on the subcategory
    var data = $(event.target).find("option:selected").data('index');
    for (i = data + 1; i < graph_types().length; i++) {
      var graph_type = graph_types()[i];
      if (typeof graph_type['category'] != 'undefined' || typeof graph_type['subcategory'] != 'undefined') {
        // we've added enough for this (sub)category
        break;
      }
      var temp = template.clone();
      temp.attr('value', graph_types()[i]['id']);
      temp.text(graph_type['title']);
      temp.data('index', i);
      temp.attr('id', '');
      e.append(temp);
      temp.show();
    }

    template.hide();  // we can't remove it, we have to hide it so we can reuse it again later

    var callback2 = function(event) {
      var data = $(event.target).find("option:selected").data('index');
      if (typeof data != 'undefined' && data !== null) {
        if (typeof graph_types()[data] == 'undefined') {
          throw new Error("Could not find graph type data for key " + data);
        }
        if (typeof graph_types()[data]['days'] != 'undefined' && graph_types()[data]['days']) {
          $("#add_graph_days").show();
        } else {
          $("#add_graph_days").hide();
        }
        if (typeof graph_types()[data]['delta'] != 'undefined' && graph_types()[data]['delta']) {
          $("#add_graph_delta").show();
        } else {
          $("#add_graph_delta").hide();
        }
        if (typeof graph_types()[data]['technical'] != 'undefined' && graph_types()[data]['technical']) {
          $("#add_graph_technical").show();
          $("#graph_technical").keyup();
        } else {
          $("#add_graph_technical").hide();
          $("#add_graph_period").hide();
        }
        if (typeof graph_types()[data]['arg0'] != 'undefined' && graph_types()[data]['arg0']) {
          $("#add_graph_arg0").show();
          $("#add_graph_arg0 th").html(graph_types()[data]['arg0_title']);
          populate_arg0(document, graph_types()[data]['arg0']);
        } else {
          $("#add_graph_arg0").hide();
        }
        if (typeof graph_types()[data]['string0'] != 'undefined' && graph_types()[data]['string0'] != null) {
          $("#add_graph_string0").show();
          $("#add_graph_string0 input").val(graph_types()[data]['string0']);
        } else {
          $("#add_graph_string0").hide();
        }
        // update description after updating technical data, so that changing graph types highlights graph description, not technical description
        $("#graph_description").html(graph_types()[data]['description']);
      }
    };

    e.keyup(callback2);
    e.change(callback2);
    e.keyup();
  };
  e.keyup(callback);
  e.change(callback);
  e.val(first_element);
  e.keyup();
});

// values: a sorted array of {id, name}
function populate_arg0(parent, values) {
  var i;
  var e = $(parent).find("#graph_arg0"), template = $(parent).find("#graph_arg0_template");
  template.hide();
  // delete old values
  $(e).find("option[id!='graph_arg0_template']").remove();
  var first = false;
  for (key in values) {
    if (values.hasOwnProperty(key)) {
      var temp = template.clone();
      temp.attr('value', values[key][0]);
      temp.text(values[key][1]);
      temp.attr('id', '');
      e.append(temp);
      temp.show();
      if (!first) first = values[key][0];
    }
  }
  // select the first one
  e.val(first);
  e.keyup();
}

/**
 * Fill in technical graph types.
 */
$(document).ready(function() {
  if (typeof graph_technical_types == 'undefined') {
    // we can't add graph types to the page if they're not defined
    return;
  }

  var i;
  var e = $(document).find("#graph_technical"), template = $(document).find("#graph_technical_template");
  template.hide();
  for (i = 0; i < graph_technical_types().length; i++) {
    var temp = template.clone();
    temp.attr('value', graph_technical_types()[i]['id']);
    temp.text(graph_technical_types()[i]['title']);
    temp.data('index', i);
    temp.attr('id', '');
    if (graph_technical_types()[i]['premium']) {
      temp.addClass('premium');
      /*
      if (!user_has_premium()) {
        temp.prop('disabled', 'true');
      }
      */
    }
    e.append(temp);
    temp.show();
  }
  template.remove();  // so we can't select it while hidden
  var callback = function(event) {
    var data = $(event.target).find("option:selected").data('index');
    var e = $("#graph_technical");
    if (typeof data != 'undefined') {
      $("#graph_description").html(graph_technical_types()[data]['description']);
      if (e.is(":visible") && typeof graph_technical_types()[data]['period'] != 'undefined' && graph_technical_types()[data]['period']) {
        $("#add_graph_period").show();
      } else {
        $("#add_graph_period").hide();
      }
      if (graph_technical_types()[data]['premium']) {
        $("#add_graph_technical").addClass('premium');
        if (!user_has_premium()) {
          $("#premium_warning").show();
        }
      } else {
        $("#add_graph_technical").removeClass('premium');
        $("#premium_warning").hide();
      }
    } else {
      // "none" is selected
      $("#add_graph_period").hide();
      $("#add_graph_technical").removeClass('premium');
      $("#premium_warning").hide();
    }
  };
  e.keyup(callback);
  e.change(callback);
  e.keyup();
});

/**
 * Enable graph layout editing through the "Enable layout editing" top right link.
 */
$(document).ready(function() {
  var e = $(document).find("#enable_editing");
  if (e) {
    e.change(function(event) {
      var enabled = ($(document).find("#enable_editing:checked").val());
      var targets = $(document).find(".graph_controls");
      if (enabled) {
        targets.show();
        $("#page_content").addClass("editing_enabled");
      } else {
        targets.hide();
        $("#page_content").removeClass("editing_enabled");
      }
      if (already_editing !== null) {
        hideGraphProperty(event.target, already_editing);
      }
      var targets = $(document).find(".render_time");
      if (enabled) {
        targets.show();
      } else {
        targets.hide();
      }
    });
    e.change(); // in case it's already checked at page generation time
  }
});

var already_editing = null;

/**
 * Enable inline editing of graph properties.
 */
function editGraphProperty(target, id, graph_data) {
  if (already_editing != null) {
    if (already_editing == id) {
      return; // no need to redisplay our own page
    }
    hideGraphProperty(target, already_editing);
  }

  // create a modal screen
  var e = $(document).find("#edit_graph_target_" + id);
  if (e) {
    // clone the edit form
    var temp = $(document).find("#edit_graph_form").clone(true /* withDataAndEvents */);
    temp.attr('id', '');
    temp.addClass('open_property_page');

    // add the element to the DOM, so we can trigger event handlers (see below)
    e.append(temp);

    // find the subcategory that this graph type belongs to
    var category_id = -1;
    for (var i = 0; i < graph_types().length; i++) {
      if (typeof graph_types()[i]['subcategory'] != 'undefined') {
        category_id = i;
      }
      if (graph_types()[i]['id'] == graph_data['type']) {
        $(temp).find("select[name='category']").val(category_id);
        $(temp).find("select[name='category']").keyup();    // trigger the event handler (AFTER it's been added to the DOM, otherwise the original form will get it)
        break;
      }
    }

    // update form elements
    $(temp).find("select[name='type']").val(graph_data['type']);
    $(temp).find("select[name='width']").val(graph_data['width']);
    $(temp).find("select[name='height']").val(graph_data['height']);
    $(temp).find("select[name='days']").val(graph_data['days']);
    $(temp).find("select[name='delta']").val(graph_data['delta']);
    if (typeof graph_data['technical'] == 'undefined') {
      $(temp).find("select[name='technical']").val(graph_data['technical_type']);
      $(temp).find("input[name='period']").val(graph_data['technical_period']);
    } else {
      $(temp).find("select[name='technical']").val(graph_data['technical']);
      $(temp).find("input[name='period']").val(graph_data['period']);
    }
    $(temp).find("input[name='id']").val(graph_data['id']);
    $(temp).find("input:submit").val("Update graph");

    temp.show();
    e.show();
    already_editing = id;

    $(temp).find("select[name='type']").keyup();  // trigger the event handler (AFTER it's been added to the DOM, otherwise the original form will get it)
    $(temp).find("select[name='technical']").keyup(); // trigger the event handler (AFTER it's been added to the DOM, otherwise the original form will get it)

    // and once we've reset & populated the lists of arguments, we can update the argument value
    $(temp).find("select[name='arg0']").val(graph_data['arg0']);
    $(temp).find("input[name='string0']").val(graph_data['string0']);
  }
}

function hideGraphProperty(target, id) {
  var e = $(document).find("#edit_graph_target_" + id);
  if (e) {
    var temp = $(e).find(".open_property_page");
    temp.remove();
    e.hide();
    already_editing = null;
  }
}
