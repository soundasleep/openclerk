<?php

/**
 * External APIs status.
 */

require("inc/global.php");

require("layout/templates.php");
page_header("External API Status", "page_external", array('common_js' => true));

$q = db()->prepare("SELECT * FROM external_status");
$q->execute();
$external = array();
$first_first = 0;
$sample_size = -1;
while ($e = $q->fetch()) {
	$external[$e['job_type']] = $e;
	if ($first_first == 0 || strtotime($e['job_first']) < strtotime($first_first)) {
		$first_first = $e['job_first'];
	}
	$sample_size = $e['sample_size'];
}

?>
<h1>External API Status</h1>

<p>
<?php echo htmlspecialchars(get_site_config('site_name')); ?> relies on the output of many external APIs.
This page lists the current status of each of these APIs, as collected over the last <?php echo recent_format($first_first, "", ""); ?> (<?php echo number_format($sample_size); ?> samples).
</p>

<ul class="external_list">
<?php
// we can't get this from account_data_grouped() because this also includes ticker information
$external_apis = array(
	"Address balances" => array(
		'blockchain' => '<a href="http://blockchain.info">Blockchain</a>',
		'litecoin' => '<a href="http://explorer.litecoin.net">Litecoin explorer</a>',
		'litecoin_block' => '<a href="http://explorer.litecoin.net">Litecoin explorer</a> (block count)',
	),

	"Mining pool wallets" => array(
		'poolx' => '<a href="http://pool-x.eu">Pool-x.eu</a>',
	),

	"Exchange wallets" => array(
		'mtgox' => '<a href="http://mtgox.com">Mt.Gox</a>',
		'btce' => '<a href="http://btc-e.com">BTC-E</a>',
		'litecoinglobal' => '<a href="http://litecoinglobal.com">Litecoin Global</a>',
	),

	"Exchange tickers" => array(
		'ticker_mtgox' => '<a href="http://mtgox.com">Mt.Gox</a>',
		'ticker_btce' => '<a href="http://btc-e.com">BTC-E</a>',
		'ticker_bitnz' => '<a href="http://bitnz.com">BitNZ</a>',
		'securities_litecoinglobal' => '<a href="http://litecoinglobal.com">Litecoin Global</a>',
	),

	"Other" => array(
		'generic' => "Generic API balances",
		'outstanding' => '<a href="' . htmlspecialchars(url_for('premium')) . '">Premium account</a> processing',
	),
);

function get_error_class($n) {
	if ($n <= 0.1) {
		// 0%
		return "perfect";
	} else if ($n <= 5) {
		return "good";
	} else if ($n <= 10) {
		return "ok";
	} else if ($n <= 20) {
		return "poor";
	} else if ($n <= 50) {
		return "bad";
	} else if ($n <= 75) {
		return "broken";
	} else {
		return "dead";
	}
}

foreach ($external_apis as $group_name => $group) {
	echo "<li><b>" . htmlspecialchars($group_name) . "</b><ul>\n";
	foreach ($group as $key => $title) {
		echo "<li><span class=\"title\">" . $title . "</span> ";
		if (isset($external[$key])) {
			echo "<span class=\"status_percent " . get_error_class(($external[$key]['job_errors'] / $external[$key]['job_count']) * 100) . "\">";
			echo number_format(($external[$key]['job_errors'] / $external[$key]['job_count']) * 100, 2) . "% errors";
			echo "</span>";
		} else {
			echo "<i class=\"no_data\">no data</i>";
		}
		echo "</li>\n";
	}
	echo "</ul></li>\n";
}
?>
</ul>

<p>
This data is refreshed automatically once every hour.
</p>

<?php
page_footer();
