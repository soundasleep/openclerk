<?php echo htmlspecialchars(get_site_config('site_name')); ?>&trade; &copy; 2013<?php if (date('Y') != 2013) echo "-" . date('Y'); ?>,
<?php echo t("powered by :openclerk", array(':openclerk' => '<a href="http://openclerk.org" target="_blank">openclerk.org</a>')); ?> <?php echo htmlspecialchars(get_site_config('openclerk_version')); ?>
<?php echo " - "; ?>
<a href="<?php echo htmlspecialchars(url_for('terms')); ?>"><?php echo ht("Terms of Service"); ?></a>
<?php echo " - "; ?><a href="<?php echo htmlspecialchars(url_for('terms#privacy')); ?>"><?php echo ht("Privacy Policy"); ?></a><br>
<div class="donate"><a href="http://code.google.com/p/openclerk/wiki/Donating" target="_blank"><?php echo ht("Donate"); ?></a>: <?php echo crypto_address('btc', '17eTMdqaFRSttfBYB9chKEzHubECZPTS6p'); ?> - <?php echo crypto_address('ltc', 'LbYmauLERxK1vyqJbB9J2MNsffsYkBSuVX'); ?></div>
