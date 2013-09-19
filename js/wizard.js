/**
 * Wizard page 'currencies': initialise currency selections
 */
$(document).ready(function() {
	$("#page_wizard_currencies .wizard .exchanges").hide();

	// hide/show possible exchanges
	var callback = function(event) {
		var parent = event.target;

		$(parent.parentNode).find(".exchanges").toggle();

		if ($(parent).hasClass("collapsed")) {
			$(parent).removeClass("collapsed");
		} else {
			$(parent).addClass("collapsed");
		}
	};
	$("#page_wizard_currencies a.set-exchange").click(callback);

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
	// and call the callback to refresh text
	$("#page_wizard_currencies .exchanges input[type=checkbox]").change();
});

/**
 * Wizard page 'pools': initialise currency selections
 */
$(document).ready(function() {
	var callback = function(event) {
		var exchanges = available_exchanges();
		for (var i = 0; i < exchanges.length; i++) {
			if (exchanges[i]['exchange'] == $(event.target).val()) {
				// selected exchange: remove any fields that were added before
				// TODO we could store the saved fields for adding back later
				$("form.wizard-add-account .added-field").remove();

				// for every input
				var inputs = exchanges[i]['inputs'];
				for (var j = 0; j < inputs.length; j++) {
					var temp = $("#add_account_template").clone();
					temp.addClass("added-field");

					var tempInput = temp.find("input");
					tempInput.attr('name', inputs[j]['key']);
					tempInput.attr('id', 'input_' + inputs[j]['key']);
					tempInput.attr('maxlength', inputs[j]['length']);
					tempInput.attr('size', inputs[j]['length'] * 2/3);

					var tempTitle = temp.find("label");
					tempTitle.html(inputs[j]['title'] + ":");
					tempTitle.attr('for', 'input_' + inputs[j]['key']);

					temp.insertBefore($("#add_account_template"));
					temp.show();
				}
			}
		}
	};

	$("form.wizard-add-account select#type").change(callback);
	// call callback to initialise first field
	$("form.wizard-add-account select#type").change();
});