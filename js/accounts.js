/**
 * Enable account title editing.
 */
$(document).ready(function() {
	var callback = function(event) {
		// hide all existing fields
		$(".standard_account_list .title span").show();
		$(".standard_account_list .title form").hide();

		var parent = event.target;
		for (var i = 0; i < 5; i++) {
			if ($(parent).attr('id')) {
				break;
			} else {
				parent = parent.parentNode;
			}
		}
		$(parent).find("span").hide();
		var forms = $(parent).find("form");
		var inputs = $(parent).find("form input[type='text']");
		inputs.keypress(function(e) {
			// submit form on enter
			if (e.which == 13) {
				forms.submit();
				return false;
			}
		});
		inputs.click(function(e) { return false; });
		forms.show();
		inputs.focus();

		return false;
	};
	$("td.title span").click(callback);

	// a simple hack to allow inputs to be hidden, without having to keep track of focus parents etc
	$("body").click(function(e) {
		$(".title form").hide();
		$(".title span").show();
	});
});

/**
 * Enable sorting.
 */
$(document).ready(function() {
	var headings = $(".standard_account_list thead th");
	headings.each(function(index, e) {
		if (!$(e).text().trim()) {
			// ignore button rows
			return;
		}
		var temp = $("#sort_buttons_template").clone();

		// set up events
		temp.find(".sort_up").click(function(e) {
			$(e.target).addClass("selected");
			temp.find(".sort_down").removeClass("selected");
			sortBy(index, true);
		});
		temp.find(".sort_down").click(function(e) {
			$(e.target).addClass("selected");
			temp.find(".sort_up").removeClass("selected");
			sortBy(index, false);
		});

		$(e).append(temp);
		temp.show();
	});
});

function sortBy(index, ascending) {
	// get the contents
	var tbody = $(".standard_account_list tbody");
	var rows = $(tbody).find("tr");
	$(rows).detach();

	// now sort through them
	$(rows).each(function(i, e) {
		var currentRows = $(tbody).find("tr");
		var rowKey = $(e).find("td:eq(" + index + ")");
		$(currentRows).each(function(i2, e2) {
			var curKey = $(e2).find("td:eq(" + index + ")");
			if (ascending && rowKey.text().trim() < curKey.text().trim()) {
				$(e).insertBefore(e2);
				return false;
			} else if (!ascending && rowKey.text().trim() > curKey.text().trim()) {
				$(e).insertBefore(e2);
				return false;
			}
		});
		if (!$(e).is(":visible")) {
			// not anywhere; add it to the end
			$(tbody).append(e);
		}
		return true;
	});

}
