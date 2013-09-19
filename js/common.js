/**
 * A basic tabs implementation.
 * Requires jQuery.
 */
function initialise_tabs(tab_query) {
	$(tab_query).find(".tab_list > li").click(function(e) {
		var parent = e.target;
		if (!$(parent).attr('id')) {
			// fix so that we can have <span>s in tab headings
			parent = parent.parentNode;
		}

		var new_tab_id = $(parent).attr('id') + "_tab";

		// hide all tab contents except this one
		$(tab_query).find(".tab_groups > li:not(#" + new_tab_id + ")").hide();
		$(tab_query).find(".tab_groups > #" + new_tab_id).show();

		// highlight this tab as selected
		$(tab_query).find(".tab_list > li:not(#" + $(parent).attr('id') + ")").removeClass("tab_selected");
		$(tab_query).find(".tab_list > li#" + $(parent).attr('id')).addClass("tab_selected");

		window.location.hash = $(parent).attr('id').replace('tab_', '');
	});

	// hook up with any anchors (on this page) that reference this tab
	// assumes #tab_ids are site-unique
	$(tab_query).find(".tab_list > li").each(function(index, e) {
		var hash = $(e).attr('id').replace('tab_', '');
		$(document).find("a[href$='#" + hash + "']").click(function(f) {
			$(e).click();
		});
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