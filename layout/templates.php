<?php

function page_header($page_title, $page_id = false, $is_admin = false, $options = array()) {

	define('PAGE_RENDER_START', microtime(true));
	header('Content-type: text/html; charset=utf-8');

?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo htmlspecialchars($page_title); ?><?php if ($is_admin) echo " [admin]"; ?></title>
    <link rel="stylesheet" type="text/css" href="default.css" />
    <?php if ($is_admin) { ?>
    <link rel="stylesheet" type="text/css" href="admin.css" />
    <?php } ?>
    <?php if (isset($options["refresh"])) { ?>
    <meta http-equiv="refresh" content="<?php echo htmlspecialchars($options['refresh']); ?>">
    <?php } ?>
    <?php if (isset($options["jquery"]) && $options["jquery"]) { ?>
    <script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>
    <?php } ?>
    <?php if (isset($options["common_js"]) && $options["common_js"]) { ?>
    <script type="text/javascript" src="js/common.js"></script>
    <?php } ?>
</head>
<body<?php if ($page_id) echo ' id="' . $page_id . '"'; ?>>
<div class="body_wrapper">

<div id="navigation">
<ul>
	<li class="home"><a href="<?php echo url_for('index'); ?>">Home</a></li>
	<?php if (user_logged_in()) { ?>
		<li><a href="<?php echo url_for('profile'); ?>">Your Account</a></li>
		<li><a href="<?php echo url_for('login', array('logout' => 1)); ?>">Logout</a></li>
		<?php if (is_admin()) { ?>
			<li class="admin"><a href="<?php echo url_for('status'); ?>">System Status</a></li>
		<?php } ?>
	<?php } else { ?>
		<li><a href="<?php echo url_for('signup'); ?>">Signup</a></li>
		<li><a href="<?php echo url_for('login'); ?>">Login</a></li>
	<?php } ?>
	<li><a href="<?php echo url_for('help'); ?>">Help</a></li>
</ul>
</div>

<?php if (did_autologin()) { ?>
<div id="autologin">
	Automatically logged in. Hi, <a href="<?php echo url_for('profile'); ?>" class="disabled"><?php echo $_SESSION["user_name"] ? htmlspecialchars($_SESSION["user_name"]) : "<i>anonymous</i>"; ?></a>! (<a href="<?php echo url_for('login', array('logout' => 1)); ?>">This isn't me.</a>)	<?php /* remove quoted string: '*/ ?>
</div>
<?php } ?>

	<div id="page_content">
<?php

}

function page_footer() {

?>
	</div>
</div>

<div id="copyright">
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> &copy; 2013<?php if (date('Y') != 2013) echo "-" . date('Y'); ?><br>
	<a href="mailto:<?php echo htmlspecialchars(get_site_config('site_email')); ?>">Contact</a>
</div>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo get_site_config('google_analytics_account'); ?>']);
  _gaq.push(['_setDomainName', '<?php echo get_site_config('google_analytics_domain'); ?>']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</body>
</html>
<?php

	if (defined('PAGE_RENDER_START')) {
		$end_time = microtime(true);
		$time_diff = ($end_time - PAGE_RENDER_START) * 1000;
		echo "<!-- rendered in " . number_format($time_diff, 2) . " ms -->";
	}

}

function ltc_address($address) {
	return "<span class=\"ltc_address\"><code>" . htmlspecialchars($address) . "</code> <a class=\"inspect\" href=\"" . htmlspecialchars(get_site_config("public_explorer_url") . "/address/" . $address) . "\" title=\"Inspect with Litecoin Explorer\">?</a></span>";
}

function ltc_format($n) {
	if (!is_numeric($n)) {
		return "<span class=\"error\">" . $n . " LTC</span>";
	}

	// find the lowest precision that we need
	$prec = 8;
	for ($i = 0; $i < 7; $i++) {
		if (number_format($n, $i) == $n) {
			$prec = $i;
			break;
		}
	}
	return "<span class=\"ltc_format\" title=\"" . number_format($n, 8) . " LTC\">" . number_format($n, $prec) . " LTC</span>";
}

function ltc_transaction($txid) {
	return "<span class=\"ltc_transaction\" title=\"" . htmlspecialchars($txid) . "\"><a href=\"" . htmlspecialchars(get_site_config("public_explorer_url") . "/tx/" . $txid) . "\">" . htmlspecialchars(substr($txid, 0, 8) . "...") . "</a>
		 <a class=\"inspect\" href=\"" . htmlspecialchars(get_site_config("public_explorer_url") . "/tx/" . $txid) . "\" title=\"Inspect with Litecoin Explorer\">?</a></span>";
}
