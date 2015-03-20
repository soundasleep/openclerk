<?php
use \Openclerk\I18n;
?>

<form action="<?php echo htmlspecialchars(url_for('set_locale')); ?>" method="post" id="locale_selector">
  <select class="language-list locale locale-<?php echo htmlspecialchars(I18n::getCurrentLocale()); ?>" name="locale">
  <?php foreach (I18n::getAvailableLocales() as $locale) {
    $selected = I18n::getCurrentLocale() == $locale->getKey();
    echo "<option value=\"" . htmlspecialchars($locale->getKey()) . "\" class=\"locale locale-" . htmlspecialchars($locale->getKey()) . "\"" . ($selected ? " selected" : "") . ">" . htmlspecialchars($locale->getTitle()) . "</option>\n";
  }
  ?>
  </select>
  <input type="hidden" name="redirect" value="<?php echo htmlspecialchars(url_for(request_url_relative(), $_GET)); ?>">
</form>
