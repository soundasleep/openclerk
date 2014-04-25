
<ul class="page_list">
<?php $first = true; foreach ($pages as $page) {
	$args = array('page' => $page['id']);
	if (require_get("demo", false)) {
		$args['demo'] = require_get("demo");
	} ?>
	<li class="page_tab<?php echo htmlspecialchars($page['id']); ?><?php if (!require_get("securities", false) && (!$page_id || $page['id'] == $page_id)) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('profile', $args)); ?>">
		<?php echo htmlspecialchars($page['title']); ?>
	</a></li>
<?php $first = false; } ?>
	<?php
	$args = array();
	if (require_get("demo", false)) {
		$args['demo'] = require_get("demo");
	} ?>
	<li class="page_tabcurrencies<?php if (isset($your_currencies) && $your_currencies) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('your_currencies', $args)); ?>">
		Your Currencies
	</a></li>
	<?php
	$args = array();
	if (require_get("demo", false)) {
		$args['demo'] = require_get("demo");
	} ?>
	<li class="page_tabhashrates<?php if (isset($your_hashrates) && $your_hashrates) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('your_hashrates', $args)); ?>">
		Your Hashrates
	</a></li>
	<?php
	$args = array('securities' => 1);
	if (require_get("demo", false)) {
		$args['demo'] = require_get("demo");
	} ?>
	<li class="page_tabsecurities<?php if (require_get("securities", false)) echo " page_current"; ?> premium"><a href="<?php echo htmlspecialchars(url_for('profile', $args)); ?>">
		Your Securities (<?php echo number_format($securities_count); ?>)
	</a></li>
	<?php
	$args = array();
	if (require_get("demo", false)) {
		$args['demo'] = require_get("demo");
	} ?>
	<li class="page_tabtransactions<?php if (isset($your_transactions) && $your_transactions) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('your_transactions', $args)); ?>">
		Your Transactions <span class="new">new</span>
	</a></li>
</ul>
