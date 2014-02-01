number_format = function(x) {
	var parts = x.toString().split(".");
	parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	// up to eight digits
	if (parts.length > 1) {
		parts[1] = parts[1].substring(0, 7);
	}
	return parts.join(".");
};

/**
 * Initialise the calculator.
 */
initialise_calculator = function(parent) {
	parent = parent || document;

	var rates = get_all_rates();
	var convert = function(value1, cur1, cur2) {
		// convert string to number
		value1 = value1.replace(/,/g, "");
		if (cur1 == cur2) {
			$(parent).find("#exchange_text").html("no exchange");
			return value1;
		}
		var key = cur1 + "_" + cur2;
		if (typeof rates[key] == 'undefined') {
			return;
		}
		$(parent).find("#exchange_text").html(rates[key]['exchanges']);
		var value2 = value1 * rates[key]['rate'];
		// convert number to string
		return number_format(value2);
	};
	var change1 = function() {
		$(parent).find("#value2").val(convert($(parent).find("#value1").val(), $(parent).find("#currency1").val(), $(parent).find("#currency2").val()));
	};
	var change2 = function() {
		$(parent).find("#value1").val(convert($(parent).find("#value2").val(), $(parent).find("#currency2").val(), $(parent).find("#currency1").val()));
	};
	$(parent).find("#value1").change(change1);
	$(parent).find("#value1").keyup(change1);
	$(parent).find("#currency2").change(change1);
	$(parent).find("#currency1").change(change1);
	$(parent).find("#value2").change(change2);
	$(parent).find("#value2").keyup(change2);

	// initialise
	change1();
};

$(document).ready(function() {
	if (typeof get_all_rates != 'undefined') {
		initialise_calculator();
	}
});
