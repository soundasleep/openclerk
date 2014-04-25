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
	var tables = $(".standard_account_list");
	tables.each(function(index, table) {
		var headings = $(table).find("thead th");
		headings.each(function(index, e) {
			if (!$(e).text().trim()) {
				// ignore button rows
				return;
			}
			var temp = $("#sort_buttons_template").clone();

			// set up events
			temp.find(".sort_up").click(function(e) {
				$(document).find(".sort_down").removeClass("selected");
				$(document).find(".sort_up").removeClass("selected");
				$(e.target).addClass("selected");
				sortBy(table, index, true);
			});
			temp.find(".sort_down").click(function(e) {
				$(document).find(".sort_down").removeClass("selected");
				$(document).find(".sort_up").removeClass("selected");
				$(e.target).addClass("selected");
				sortBy(table, index, false);
			});

			$(e).append(temp);
			temp.show();
		});

		// sort by title on page load
		if ($(table).find("thead th.default_sort_up").length > 0) {
			$(table).find("thead th.default_sort_up").find(".sort_up").click();
		} else if ($(table).find("thead th.default_sort_down").length > 0) {
			$(table).find("thead th.default_sort_down").find(".sort_down").click();
		} else {
			$(headings[0]).find(".sort_up").click();
		}
	});
});

function sortBy(table, index, ascending) {
	// get the contents
	var tbody = $(table).find("tbody");
	var rows = $(tbody).find("tr");
	$(rows).detach();

	// go through and find row values and indexes
	var temp = [];
	$(rows).each(function(i, e) {
		var td = $(e).find("td:eq(" + index + ")");
		temp.push({'index': i, 'key': getSortValue(td)});
	});

	temp.sort(function(a, b) {
		if (a.key == b.key) return 0;
		if (a.key < b.key) return ascending ? -1 : 1;
		return ascending ? 1 : -1;
	});

	// now add them back in
	for (var i = 0; i < temp.length; i++) {
		$(tbody).append(rows[temp[i].index]);
	}

	// fix row colours
	var count = 0;
	$(table).find("tbody tr").each(function(i, e) {
		$(e).removeClass('even');
		$(e).removeClass('odd');
		if (count++ % 2 == 0) {
			$(e).addClass('even');
		} else {
			$(e).addClass('odd');
		}
	});
}

function getSortValue(e) {
	if ($(e).find("span.address code").length > 0) {
		return $(e).find("span.address code").text().trim();
	}
	if ($(e).find("span.currency_format").length > 0 && $(e).find("span.currency_format").attr('title')) {
		// currency value: strip out " cur"
		var val = $(e).find("span.currency_format").attr('title').trim();
		return parseFloat(val.substring(0, val.lastIndexOf(" ")).replace(",", ""));
	}
	if ($(e).find("span").length > 0 && $(e).find("span").attr('title')) {
		return $(e).find("span").attr('title').trim();
	}
	return $(e).text().trim();
}

function confirmAccountDelete() {
  return confirm("Are you sure you want to delete this account?\n\nThis will delete all historical data for this account, but will not update any historical summary calculations which may have used this data.\n\nYou may wish to disable this account instead, which will retain historical account data and keep your historical summaries correct, by clicking \"Cancel\" and using the \"Disable\" button instead.");
}
