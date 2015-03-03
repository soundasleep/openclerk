/**
 * A basic tabs implementation.
 * Requires jQuery.
 */
function initialise_tabs(tab_query) {
  $(tab_query).find(".tab_list > li").click(function(e) {
    var parent = e.target;
    if (!$(parent).attr('id')) {
      // fix so that we can have <span>s in tab headings
      parent = parent.parentNode;
    }

    var new_tab_id = $(parent).attr('id') + "_tab";

    // hide all tab contents except this one
    $(tab_query).find(".tab_groups > li:not(#" + new_tab_id + ")").hide();
    $(tab_query).find(".tab_groups > #" + new_tab_id).show();

    // highlight this tab as selected
    $(tab_query).find(".tab_list > li:not(#" + $(parent).attr('id') + ")").removeClass("tab_selected");
    $(tab_query).find(".tab_list > li#" + $(parent).attr('id')).addClass("tab_selected");

    window.location.hash = $(parent).attr('id').replace('tab_', '');
  });

  // hook up with any anchors (on this page) that reference this tab
  // assumes #tab_ids are site-unique
  $(tab_query).find(".tab_list > li").each(function(index, e) {
    var hash = $(e).attr('id').replace('tab_', '');
    $(document).find("a[href$='#" + hash + "']").click(function(f) {
      $(e).click();
    });
  });

  // get the runtime tab, unless the runtime tab doesn't actually exist
  var tab_hash = window.location.hash ? window.location.hash.substring(1) : false;
  if (tab_hash && $(tab_query).find(".tab_list > li#tab_" + tab_hash).length == 0) {
    tab_hash = false;
  }

  // the first tab is selected
  if (tab_hash) {
    $($(tab_query).find(".tab_list > li#tab_" + tab_hash)).addClass("tab_selected");
  } else {
    $($(tab_query).find(".tab_list > li")[0]).addClass("tab_selected");
  }

  // hide all except the first one
  $(tab_query).find(".tab_groups > li").hide();
  $(tab_query).find(".tab_groups > li").addClass("tab_open");
  if (tab_hash) {
    $($(tab_query).find(".tab_groups > li#tab_" + tab_hash + "_tab")).show();
  } else {
    $($(tab_query).find(".tab_groups > li")[0]).show();
  }
}

/**
 * There's no need to forcibly make all pages with tabs also call
 * {@link #initialise_tabs()}; we can just do it here.
 */
$(document).ready(function() {
  $(".tabs").each(function (i, e) {
    initialise_tabs(e);
  });
});

/**
 * A basic implementation of collapsing content.
 */
$(document).ready(function() {

  // hide by default
  $(".collapse-target").hide();

  // enable toggle
  var callback = function(event) {
    var parent = event.target;

    $(parent.parentNode).find(".collapse-target").toggle();

    if ($(parent).hasClass("collapsed")) {
      $(parent).removeClass("collapsed");
    } else {
      $(parent).addClass("collapsed");
    }
  };
  $(".collapse-link").click(callback);

});

/**
 * Allow AJAX requests for graphs to be queued, so that they aren't all queued up
 * at once. This should improve both client-side and server-side performance.
 * Queued requests are executed in the order that they are initialised through
 * queue_ajax_request().
 */
var queued_ajax_requests = new Array();
var pending_ajax_requests = 0;
var max_ajax_requests = 10;

/**
 * Instead of $.ajax(url, obj), use queue_ajax_request(url, obj).
 * Up to 5 requests will be sent simultaneously.
 */
function queue_ajax_request(url, obj, dataType) {
  queued_ajax_requests.push({'url': url, 'obj': obj, 'dataType': dataType});
  execute_queued_ajax_request();
}

/**
 * Possibly execute a queued ajax request.
 */
function execute_queued_ajax_request() {
  if (queued_ajax_requests.length <= 0) {
    return; // nothing to do here
  }
  if (pending_ajax_requests < max_ajax_requests) {
    // it's ok, we can send one now
    pending_ajax_requests++;
    var obj = queued_ajax_requests.shift();
    $.ajax(obj.url, {
      'success': function(data, text, xhr) {
        obj.obj.success(data, text, xhr);
        pending_ajax_requests--;
        execute_queued_ajax_request();  // maybe execute the next queued one
      },
      'error': function(xhr, text, error) {
        obj.obj.error(xhr, text, error);
        pending_ajax_requests--;
        execute_queued_ajax_request();  // maybe execute the next queued one
      },
      'dataType': obj.dataType
    });
  }
}

/**
 * Locale switching.
 */
$(document).ready(function() {
  $("#locale_selector select").change(function() {
    $("#locale_selector").submit();
  });
});
