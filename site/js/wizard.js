/**
 * Wizard page 'currencies': initialise currency selections
 */
$(document).ready(function() {
  // update exchange text
  var callback2 = function(event) {
    var parent = event.target;

    // get all exchanges
    var ex = $(parent.parentNode /* li */.parentNode /* ul */).find("input:checked");
    var list = [];
    ex.each(function(i, e) {
      list.push($(e.parentNode).find("label").html());
    });

    // get default exchange
    var defaultExchange = $(parent.parentNode /* li */.parentNode /* ul */).find("label.default-exchange").html();

    $(parent.parentNode.parentNode.parentNode.parentNode).find(".exchange-text").text("Exchange" + (list.length > 1 ? "s" : "") + ": " + (list.length == 0 ? defaultExchange : list.join(", ")));

    // select the parent currency checkbox if necessary
    $(parent.parentNode.parentNode.parentNode.parentNode.parentNode).find("input.parent-currency").prop('checked', list.length != 0);
  };

  $("#page_wizard_currencies .exchanges input[type=checkbox]").change(callback2);
  $("#page_wizard_currencies .exchanges input[type=checkbox]").keypress(callback2);     // for keyboard navigation
  // and call the callback to refresh text
  $("#page_wizard_currencies .exchanges input[type=checkbox]").change();
});

/**
 * Wizard page 'accounts': initialise input fields and help pages
 */
$(document).ready(function() {
  var callback = function(event) {
    var exchanges = available_exchanges();
    for (var i = 0; i < exchanges.length; i++) {
      if (exchanges[i]['exchange'] == $(event.target).val()) {
        var key = $(event.target).val();

        // selected exchange: remove any fields that were added before
        // TODO we could store the saved fields for adding back later
        $("form.wizard-add-account .added-field").remove();

        // for every input
        var inputs = exchanges[i]['inputs'];
        for (var j = 0; j < inputs.length; j++) {
          // dropdown or normal input?
          var dropdown = (typeof inputs[j]['dropdown'] != 'undefined') ? inputs[j]['dropdown'] : false;
          var checkbox = (typeof inputs[j]['checkbox'] != 'undefined') ? inputs[j]['checkbox'] : false;
          var temp = $(dropdown ? "#add_account_template_dropdown" : checkbox ? "#add_account_template_checkbox" : "#add_account_template").clone();
          temp.addClass("added-field");

          var tempInput = temp.find(dropdown ? "select" : "input");
          tempInput.attr('name', inputs[j]['key']);
          tempInput.attr('id', 'input_' + inputs[j]['key']);
          if (dropdown) {
            var tempOption = temp.find("#option_template");
            for (var option_key in dropdown) {
              if (dropdown.hasOwnProperty(option_key)) {
                var tempOption1 = tempOption.clone();
                tempOption1.val(option_key);
                tempOption1.text(dropdown[option_key]);
                tempOption1.attr('id', '');
                if (typeof inputs[j]['style_prefix'] != 'undefined') {
                  tempOption1.addClass(inputs[j]['style_prefix'] + option_key);
                }

                // we want to sort by text when inserting in children, so we sort in-place
                var previous = $(tempInput).children();
                var ia = false;
                previous.each(function(index, element) {
                  ia = element;
                  if ($(element).attr('id')) {
                    // we don't want to process the template
                    return true;
                  }

                  if ($(element).text() >= dropdown[option_key]) {
                    return false;
                  }
                });

                $(ia).before(tempOption1);
              }
            }
            tempOption.remove();
          } else {
            tempInput.attr('maxlength', inputs[j]['length']);
            tempInput.attr('size', 20 + (Math.min(64, inputs[j]['length']) * 1/5));
            if (typeof inputs[j]['default'] != 'undefined') {
              tempInput.val(inputs[j]['default']);
            }
          }
          // set value if it's been provided
          var previous = previous_data();
          if (previous && typeof previous['type'] != 'undefined' && previous['type'] == key) {
            if (typeof previous[inputs[j]['key']] != 'undefined') {
              tempInput.val(previous[inputs[j]['key']]);
            }
          }

          var tempTitle = temp.find("label");
          tempTitle.html(inputs[j]['title'] + (checkbox ? "" : ":"));
          tempTitle.attr('for', 'input_' + inputs[j]['key']);

          temp.insertBefore($("#wizard_account_table tr.buttons"));
          temp.show();
        }

        // display unsafe warning text
        if (typeof exchanges[i]['unsafe'] != 'undefined' && exchanges[i]['unsafe']) {
          var temp = $("#add_account_unsafe_template").clone();
          temp.addClass("added-field");

          temp.find("label").html(exchanges[i]['unsafe']);

          temp.insertBefore($("#wizard_account_table tr.buttons"));
          temp.show();
        }

        // display associated help, stored in accounts_help div
        var target = $("#accounts_help_target");
        var temp = $("#accounts_help #accounts_help_" + key);
        target.html(temp.html());

        // clean up link display etc
        var link_text = target.find("a.wizard_link").text();
        target.find("a.wizard_link").replaceWith(link_text);      // replace link with text

        // link up instructions_add heading with help page
        var help_text = target.find(".instructions_add h2").text();
        target.find(".instructions_add h2").text("");
        target.find(".instructions_add h2").append(target.find(".more_help").clone().find("a").text(help_text));

        // add collapse functionality to safety text
        var collapseCallback = function(event) {
          var parent = $(event.target);
          if ($(parent).hasClass("collapsed")) {
            $(parent).removeClass("collapsed");
            $(parent.parent(0)).find("ul").removeClass("collapsed");
          } else {
            $(parent).addClass("collapsed");
            $(parent.parent(0)).find("ul").addClass("collapsed");
          }
        };
        target.find(".instructions_safe h2").click(collapseCallback);
        // collapse by default
        target.find(".instructions_safe h2").click();
      }
    }
  };

  $("form.wizard-add-account select#type").change(callback);
  $("form.wizard-add-account select#type").keypress(callback);    // for keyboard navigation
  // call callback to initialise first field
  $("form.wizard-add-account select#type").change();
});

/**
 * Wizard page 'reports': initialise warning messages
 */
$(document).ready(function() {
  var callback = function(event) {
    var parent = $(event.target);
    // hide all other warnings
    $("#page_wizard_reports .reset-warning").hide();

    var warnings = $(parent.parent(0).parent(0)).find(".reset-warning");
    if (parent.is(":checked")) {
      warnings.show();
    } else {
      warnings.hide();
    }
  };

  $("#page_wizard_reports input[type=radio]").change(callback);
  $("#page_wizard_reports input[type=radio]").keypress(callback);   // for keyboard navigation

  // hide all other warnings
  $("#page_wizard_reports .reset-warning").hide();
});

var callback_intervals = {};
var pending_urls = {};

/**
 * Callback function to initialise a "waiting..." icon on something that's being tested, and
 * polls the site to find out the result
 */
function initialise_wizard_test_callback(element, url) {
  if (typeof pending_urls[url] != 'undefined') {
    // we're already waiting for this URL
    return;
  }
  pending_urls[url] = true;

  callback_intervals[url] = window.setInterval(function() {
    $.ajax(url, {
      'success': function(data, status, xhr) {
        delete pending_urls[url];
        $(element).html(data);
        // once the test has succeeded, stop requesting the same content over and over
        if (data.indexOf('successful test') >= 0) {
          window.clearInterval(callback_intervals[url]);
        }
      },
    });
  }, 10000 /* ms */);
}
