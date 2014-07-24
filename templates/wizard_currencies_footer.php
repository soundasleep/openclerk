
<p>
	<?php echo t("You can also :vote_for_new_currencies not listed here with your :site_name account :new_marker.",
	array(
		':vote_for_new_currencies' => link_to(url_for('vote_coins'), t('vote for new currencies')),
		':new_marker' => "<span class=\"new\">" . ht("new") . "</span>",
	)); ?>
</p>

<div style="clear:both;"></div>

</div><?php /* ends wizard-content div */ ?>
