/**
 * Client-side code for notifications.
 */

$(document).ready(function() {
	$("#notification_type").change(function(e) {
		switch (e.target.value) {
			case "ticker":
				$(".notification_template .exchanges").show();
				$("#notification_exchanges").change();	// trigger change
				break;

			default:
				alert("Unknown target value " + e.target.value);
		}
	});

	var supported = get_supported_notifications();
	$("#notification_exchanges").change(function(e) {
		// find all the pairs for this exchange
		var exchange = e.target.value;
		// hide them all
		$("#notification_currencies option").hide();
		if (typeof supported['exchanges'][exchange] != 'undefined') {
			for (var i = 0; i < supported['exchanges'][exchange].length; i++) {
				$("#notification_currencies option[value='" + supported['exchanges'][exchange][i][0] + supported['exchanges'][exchange][i][1] + "']").show();
			}
		}
		// if the previously selected pair no longer exists, select the first new one as a default
		if ($("#notification_currencies").val() != $("#notification_currencies option:visible").first().val()) {
			$("#notification_currencies").val($("#notification_currencies option:visible").first().val());
		}
		$("#notification_currencies").change();		// update value_labels, val() does not call change()
	});

	// when changing the currency, change the label
	$("#notification_currencies").change(function(e) {
		$(".notification_template .value_label").html($(e.target).find(":selected").html());
	});

	$("#notification_condition").change(function(e) {
		// if having a value is invalid, hide it
		switch (e.target.value) {
			case "increases":
			case "decreases":
				$(".notification_template .notification_value").hide();
				break;

			default:
				$(".notification_template .notification_value").show();
		}

		// if % isn't a valid option, hide it
		switch (e.target.value) {
			case "increases_by":
			case "decreases_by":
				$(".notification_template .notification_percent_on").show();
				$(".notification_template .notification_percent_off").hide();
				break;

			default:
				$(".notification_template .notification_percent_on").hide();
				$(".notification_template .notification_percent_off").show();

		}
	});

	// and trigger the first change
	$("#notification_type").change();
});
