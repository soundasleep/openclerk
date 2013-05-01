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
	});

	// the first tab is selected
	$($(tab_query).find(".tab_list > li")[0]).addClass("tab_selected");

	// hide all except the first one
	$(tab_query).find(".tab_groups > li:not(:first)").hide();
	$(tab_query).find(".tab_groups > li").addClass("tab_open");
	$($(tab_query).find(".tab_groups > li")[0]).show();
}