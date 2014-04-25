
<ul class="page_list">
	<?php if (is_admin()) { ?>
		<?php
		$args = array();
		if (require_get("demo", false)) {
			$args['demo'] = require_get("demo");
		} ?>
		<li class="page_tabtransactions<?php if (isset($your_transactions) && $your_transactions) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('your_transactions', $args)); ?>">
			Your Transactions <span class="new">new</span>
		</a></li>
	<?php } ?>
</ul>
