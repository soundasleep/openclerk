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
