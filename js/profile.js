/**
 * Add graph functionality.
 */
$(document).ready(function() {
	var i;
	var e = $(document).find("#graph_type"), template = $(document).find("#graph_type_template");
	template.hide();
	for (i = 0; i < graph_types().length; i++) {
		var temp = template.clone();
		temp.attr('value', graph_types()[i]['id']);
		temp.text(graph_types()[i]['title']);
		temp.data('index', i);
		temp.attr('id', '');
		e.append(temp);
		temp.show();
		if (i == 0) {
			temp.select();
			temp.attr('selected', 'selected');
			$("#graph_description").html(graph_types()[i]['description']);
		}
	}
	e.change(function(event) {
		var data = $(event.target).find("option:selected").data('index');
		$("#graph_description").html(graph_types()[data]['description']);
	});
});

/**
 * Enable graph layout editing.
 */
$(document).ready(function() {
	var e = $(document).find("#enable_editing");
	if (e) {
		e.change(function(event) {
			var enabled = ($(document).find("#enable_editing:checked").val());
			var targets = $(document).find(".graph_controls");
			if (enabled) {
				targets.show();
			} else {
				targets.hide();
			}
			var targets = $(document).find(".render_time");
			if (enabled) {
				targets.show();
			} else {
				targets.hide();
			}
		});
		e.change(); // in case it's already checked at page generation time
	}
});