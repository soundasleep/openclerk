/**
 * Initialise the calculator.
 */
$(document).ready(function() {
	var rates = get_all_rates();
	var convert = function(value1, cur1, cur2) {
		// convert string to number
		value1 = value1.replace(/,/g, "");
		if (cur1 == cur2) {
			$("#exchange_text").html("no exchange");
			return value1;
		}
		var key = cur1 + "_" + cur2;
		if (typeof rates[key] == 'undefined') {
			alert("Found no rate for " + cur1 + "/" + cur2);
			return;
		}
		$("#exchange_text").html(rates[key]['exchanges']);
		var value2 = value1 * rates[key]['rate'];
		// convert number to string
		return number_format(value2);
	}
	var change1 = function() {
		$("#value2").val(convert($("#value1").val(), $("#currency1").val(), $("#currency2").val()));
	};
	var change2 = function() {
		$("#value1").val(convert($("#value2").val(), $("#currency2").val(), $("#currency1").val()));
	};
	$("#value1").change(change1);
	$("#value1").keyup(change1);
	$("#currency2").change(change1);
	$("#currency1").change(change1);
	$("#value2").change(change2);
	$("#value2").keyup(change2);

	// initialise
	change1();
});

number_format = function(x) {
	var parts = x.toString().split(".");
	parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	// up to eight digits
	if (parts.length > 1) {
		parts[1] = parts[1].substring(0, 7);
	}
	return parts.join(".");
};
