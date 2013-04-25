<?php

/**
 * This page does the hard work of displaying what accounts a user currently has enabled.
 * We delegate adding/deleting accounts to each of the separate account pages.
 */

require("inc/global.php");
require_login();

require("layout/templates.php");
page_header("Your Accounts", "page_accounts");

$user = get_user(user_id());
if (!$user) {
	throw new Exception("Could not find self user.");
}

$messages = array();
if (get_temporary_messages()) {
	$messages += get_temporary_messages();
}

// get all of our accounts
$accounts = array();

$account_data_grouped = array(
	'Addresses' => array(
		'blockchain' => array('url' => 'accounts_blockchain', 'title' => 'BTC addresses', 'label' => 'address', 'labels' => 'addresses', 'table' => 'addresses'),
	),
	'Mining pools' => array(
		'poolx' => array('url' => 'accounts_poolx', 'title' => 'Pool-X.eu accounts', 'label' => 'account', 'table' => 'accounts_poolx'),
	),
	'Exchanges' => array(
		'mtgox' => array('url' => 'accounts_mtgox', 'title' => 'Mt.Gox accounts', 'label' => 'account', 'table' => 'accounts_mtgox'),
		'btce' => array('url' => 'accounts_btce', 'title' => 'BTC-E accounts', 'label' => 'account', 'table' => 'accounts_btce'),
	),
	'Other' => array(
		'generic' => array('url' => 'accounts_generic', 'title' => 'Generic APIs', 'label' => 'API', 'table' => 'accounts_generic'),
	),
);

foreach ($account_data_grouped as $group) {
	foreach ($group as $key => $data) {
		$q = db()->prepare("SELECT COUNT(*) AS c FROM " .  $data['table'] . " WHERE user_id=?");
		$q->execute(array(user_id()));
		$accounts[$key] = $q->fetch()['c'];
	}
}

?>

<?php if ($messages) { ?>
<div class="message">
<ul>
	<?php foreach ($messages as $m) { echo "<li>" . $m . "</li>"; } /* do NOT accept user input for messages! */ ?>
</ul>
</div>
<?php } ?>

<h1>Your Accounts</h1>

<ul class="account_list">
<?php

foreach ($account_data_grouped as $label => $account_data) {
	echo "<li>" . htmlspecialchars($label) . "\n<ul>\n";
	foreach ($account_data as $key => $value) {
		// if we don't specify a plural, we assume it's just adding 's'
		if (!isset($value['labels']))
			$value['labels'] = $value['label'] . "s";

		echo "<li><strong>" . htmlspecialchars($value['title']) . ":</strong> ";
		echo "<a href=\"" . url_for($value['url']) . "\">";
		echo (isset($accounts[$key]) && $accounts[$key]) ? (number_format($accounts[$key]) . " " . ($accounts[$key] == 1 ? $value['label'] : $value['labels'])) : "<i>none</i>";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul></li>\n";
}
?>
</ul>

<?php
page_footer();
