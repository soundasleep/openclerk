<form action="<?php echo htmlspecialchars(url_for('set_locale')); ?>" method="post" id="locale_selector">
	<select class="language-list locale locale-<?php echo htmlspecialchars(get_current_locale()); ?>" name="locale">
	<?php foreach (get_all_locales() as $locale) {
		$selected = get_current_locale() == $locale;
		echo "<option value=\"" . htmlspecialchars($locale) . "\" class=\"locale locale-" . htmlspecialchars($locale) . "\"" . ($selected ? " selected" : "") . ">" . htmlspecialchars(get_locale_label($locale)) . "</option>\n";
	}
	?>
	</select>
	<input type="hidden" name="redirect" value="<?php echo htmlspecialchars(url_for(request_url_relative(), $_GET)); ?>">
</form>
