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

