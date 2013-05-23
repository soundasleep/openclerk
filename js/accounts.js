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
	$(".standard_account_list tbody tr").each(function(i, e) {
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
	if ($(e).find("span").length > 0 && $(e).find("span").attr('title')) {
		return $(e).find("span").attr('title').trim();
	}
	return $(e).text().trim();
}
