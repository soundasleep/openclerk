/**
 * A basic tabs implementation.
 * Requires jQuery.
 */
function initialise_tabs(tab_query) {
	$(tab_query).find(".tab_list > li").click(function(e) {
		var new_tab_id = $(e.target).attr('id') + "_tab";

		// hide all tab contents except this one
		$(tab_query).find(".tab_groups > li:not(#" + new_tab_id + ")").hide();
		$(tab_query).find(".tab_groups > #" + new_tab_id).show();

		// highlight this tab as selected
		$(tab_query).find(".tab_list > li:not(#" + $(e.target).attr('id') + ")").removeClass("tab_selected");
		$(tab_query).find(".tab_list > li#" + $(e.target).attr('id')).addClass("tab_selected");

		window.location.hash = $(e.target).attr('id').replace('tab_', '');
	});

	// get the runtime tab, unless the runtime tab doesn't actually exist
	var tab_hash = window.location.hash ? window.location.hash.substring(1) : false;
	if (tab_hash && $(tab_query).find(".tab_list > li#tab_" + tab_hash).length == 0) {
		tab_hash = false;
	}

	// the first tab is selected
	if (tab_hash) {
		$($(tab_query).find(".tab_list > li#tab_" + tab_hash)).addClass("tab_selected");
	} else {
		$($(tab_query).find(".tab_list > li")[0]).addClass("tab_selected");
	}

	// hide all except the first one
	$(tab_query).find(".tab_groups > li").hide();
	$(tab_query).find(".tab_groups > li").addClass("tab_open");
	if (tab_hash) {
		$($(tab_query).find(".tab_groups > li#tab_" + tab_hash + "_tab")).show();
	} else {
		$($(tab_query).find(".tab_groups > li")[0]).show();
	}
}