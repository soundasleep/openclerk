var last_click = false;
var all_expanded = false;

$(document).ready(function() {
	$("dl.help_list dt").click(function(e) {
		$("dl.help_list dd").hide();
		if (last_click == e.target) {
			$($(e.target).find("~ dd")[0]).hide();
			last_click = false;
		} else {
			$($(e.target).find("~ dd")[0]).show();
			last_click = e.target;
		}
	});
	$("dl.help_list dd").hide();

	$("#expand_all").click(function(e) {
		if (all_expanded) {
			$("dl.help_list dd").hide();
		} else {
			$("dl.help_list dd").show();
		}
		all_expanded = !all_expanded;
	});
});