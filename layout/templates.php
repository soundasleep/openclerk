<?php

function page_header($page_title, $page_id = false, $options = array()) {

	define('PAGE_RENDER_START', microtime(true));
	header('Content-type: text/html; charset=utf-8');

?>
<!DOCTYPE HTML>
<html<?php if (has_required_admin()) { echo " class=\"body_admin\""; } ?>>
<head>
    <title><?php echo htmlspecialchars($page_title); ?><?php if (has_required_admin()) echo " [admin]"; ?></title>
    <link rel="stylesheet" type="text/css" href="default.css" />
    <?php if (get_site_config('custom_css')) { ?>
    <link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars(get_site_config('custom_css')); ?>" />
    <?php } ?>
    <?php if (has_required_admin()) { ?>
    <link rel="stylesheet" type="text/css" href="admin.css" />
    <?php } ?>
    <?php if (isset($options["refresh"])) { ?>
    <meta http-equiv="refresh" content="<?php echo htmlspecialchars($options['refresh']); ?>">
    <?php } ?>
    <?php if (isset($options["jquery"]) && $options["jquery"]) { ?>
    <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
    <?php } ?>
    <?php if (isset($options['jsapi']) && $options['jsapi']) { ?>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <?php } ?>
    <?php if (isset($options["common_js"]) && $options["common_js"]) { ?>
    <script type="text/javascript" src="js/common.js"></script>
    <?php } ?>
    <?php if (isset($options["js"]) && $options["js"]) {
    	if (!is_array($options['js'])) $options['js'] = array($options['js']);
    	foreach ($options['js'] as $js) { ?>
    <script type="text/javascript" src="js/<?php echo htmlspecialchars($js); ?>.js"></script>
    <?php }
    } ?>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body<?php if ($page_id) echo ' id="' . $page_id . '"'; ?><?php if (isset($options['class'])) echo " class=\"" . htmlspecialchars($options['class']) . "\""; ?>>
<div class="body_wrapper">

<?php require_template("templates_header"); ?>

<div id="navigation">
<ul>
	<li class="home"><a href="<?php echo url_for('index'); ?>"><?php echo htmlspecialchars(get_site_config('site_name')); ?></a></li>
	<?php if (user_logged_in()) { ?>
		<li class="profile"><a href="<?php echo url_for('profile'); ?>">Your Reports</a></li>
		<li class="accounts"><a href="<?php echo url_for('wizard_accounts'); ?>">Configure Accounts</a></li>
		<li class="user"><a href="<?php echo url_for('user'); ?>">User Profile</a></li>
		<li class="logout"><a href="<?php echo url_for('login', array('logout' => 1)); ?>">Logout</a></li>
		<?php if (is_admin()) { ?>
			<li class="admin"><a href="<?php echo url_for('admin'); ?>">System Status</a></li>
		<?php } ?>
	<?php } else { ?>
		<li class="signup"><a href="<?php echo url_for('signup'); ?>">Signup</a></li>
		<li class="login"><a href="<?php echo url_for('login'); ?>">Login</a></li>
	<?php } ?>
	<li class="premium"><a href="<?php echo url_for('premium'); ?>">Premium</a></li>
	<li class="help"><a href="<?php echo url_for('help'); ?>">Help</a></li>
</ul>
</div>

<?php if (did_autologin()) { ?>
<div id="autologin">
	Automatically logged in. Hi, <a href="<?php echo url_for('user'); ?>" class="disabled"><?php echo $_SESSION["user_name"] ? htmlspecialchars($_SESSION["user_name"]) : "<i>anonymous</i>"; ?></a>! (<a href="<?php echo url_for('login', array('logout' => 1)); ?>">This isn't me.</a>)	<?php /* remove quoted string: '*/ ?>
</div>
<?php } ?>

	<div id="page_content">
<?php

	// always display messages on every page as necessary
	display_messages();

}

function page_footer() {

?>
	</div>
</div>

<?php require_template("templates_footer"); ?>

<div id="footer_nav">
	<ul class="footer_nav_list">
		<li><span class="title"><?php echo htmlspecialchars(get_site_config('site_name')); ?></span>
			<ul>
				<li><a href="<?php echo htmlspecialchars(url_for('index')); ?>">About</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('historical')); ?>">Historical Data</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('premium')); ?>">Get Premium</a></li>
				<li><a href="http://openclerk.org" target="_blank">Openclerk.org</a></li>
			</ul>
		</li>
		<li><span class="title">Your Account</span>
			<ul>
				<?php if (user_logged_in()) { ?>
				<li><a href="<?php echo htmlspecialchars(url_for('user')); ?>">User Profile</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">Currency Preferences</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">Configure Accounts</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">Your Reports</a></li>
				<?php } else { ?>
				<li><a href="<?php echo htmlspecialchars(url_for('signup')); ?>">Signup</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('login')); ?>">Login</a></li>
				<?php } ?>
			</ul>
		</li>
		<li><span class="title">Support</span>
			<ul>
				<li><a href="<?php echo htmlspecialchars(url_for('help')); ?>">Help Centre</a></li>
				<?php if (get_site_config('forum_link')) { ?>
				<li><a href="<?php echo htmlspecialchars(get_site_config('forum_link')); ?>" target="_blank">Forums</a></li>
				<?php } ?>
				<li><a href="<?php echo htmlspecialchars(url_for('contact')); ?>">Contact Us</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('external')); ?>">External API Status</a></li>
			</ul>
		</li>
	</ul>

	<div id="copyright">
		<?php require_template("templates_copyright"); ?>
	</div>
</div>
<?php if (!(has_required_admin() || defined('BATCH_SCRIPT'))) { ?>
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
<?php } ?>
</body>
</html>
<?php

	if (defined('PAGE_RENDER_START')) {
		$end_time = microtime(true);
		$time_diff = ($end_time - PAGE_RENDER_START) * 1000;
		echo "<!-- rendered in " . number_format($time_diff, 2) . " ms -->";

		if (get_site_config('timed_sql')) {
			global $global_timed_sql;
			echo "\n<!-- SQL debug: \n " . print_r($global_timed_sql, true) . "\n-->";
		}
	}

}

/**
 * Display any errors or messages, including those passed through temporary_messages/errors.
 */
function display_messages() {
	global $messages;
	global $errors;

	if (!isset($messages)) $messages = array();
	if (!isset($errors)) $errors = array();

	if (get_temporary_messages()) {
		$messages = array_join($messages, get_temporary_messages());
	}
	if (get_temporary_errors()) {
		$errors = array_join($errors, get_temporary_errors());
	}

	if ($messages) { ?>
<div class="message">
<ul>
	<?php foreach ($messages as $m) { echo "<li>" . $m . "</li>"; } /* do NOT accept user input for messages! */ ?>
</ul>
</div>
<?php }
	if ($errors) { ?>
<div class="error">
<ul>
	<?php foreach ($errors as $m) { echo "<li>" . $m . "</li>"; } /* do NOT accept user input for messages! */ ?>
</ul>
</div>
<?php }

}

function ltc_address($address) {
	return "<span class=\"address ltc_address\"><code>" . htmlspecialchars($address) . "</code> <a class=\"inspect\" href=\"" . htmlspecialchars(get_site_config("ltc_address_url") . $address) . "\" title=\"Inspect with Litecoin Explorer\">?</a></span>";
}

function btc_address($address) {
	return "<span class=\"address btc_address\"><code>" . htmlspecialchars($address) . "</code> <a class=\"inspect\" href=\"" . htmlspecialchars(get_site_config("btc_address_url") . $address) . "\" title=\"Inspect with Blockchain\">?</a></span>";
}

function ftc_address($address) {
	return "<span class=\"address ftc_address\"><code>" . htmlspecialchars($address) . "</code> <a class=\"inspect\" href=\"" . htmlspecialchars(get_site_config("ftc_address_url") . $address) . "\" title=\"Inspect with CryptoCoin Explorer\">?</a></span>";
}

function ppc_address($address) {
	return "<span class=\"address ppc_address\"><code>" . htmlspecialchars($address) . "</code> <a class=\"inspect\" href=\"" . htmlspecialchars(get_site_config("ppc_address_url") . $address) . "\" title=\"Inspect with CryptoCoin Explorer\">?</a></span>";
}

function nvc_address($address) {
	return "<span class=\"address nvc_address\"><code>" . htmlspecialchars($address) . "</code> <a class=\"inspect\" href=\"" . htmlspecialchars(get_site_config("nvc_address_url") . $address) . "\" title=\"Inspect with CryptoCoin Explorer\">?</a></span>";
}

function currency_format($currency_code, $n, $precision = 8 /* must be 8 for issue #1 */) {
	$currency = strtoupper($currency_code);

	if (!is_numeric($n)) {
		return "<span class=\"error\">" . $n . " $currency</span>";
	}

	return "<span class=\"" . strtolower($currency) . "_format currency_format\" title=\"" . number_format_autoprecision($n, 8) . " $currency\">" . number_format_precision($n, $precision) . " $currency</span>";
}

function number_format_html($n, $precision) {
	return "<span title=\"" . number_format_autoprecision($n, 8) . "\">" . number_format_precision($n, $precision) . "</span>";
}

function capitalize($s) {
	$split = explode(" ", $s);
	foreach ($split as $i => $value) {
		$split[$i] = strtoupper(substr($value, 0, 1)) . substr($value, 1);
	}
	return implode(" ", $split);
}

/**
 * The default colours used in Google charts. Obtained by taking screenshots.
 */
function default_chart_color($index) {
	switch ($index) {
		case 0: return "#3366cc";
		case 1: return "#dc3912";
		case 2: return "#ff9900";
		case 3: return "#109618";
		case 4: return "#990099";
		case 5: return "#0099c6";
		case 6: return "#dd4477";
		case 7: return "#66aa00";
		case 8: return "#b82e2e";
	}
	// unknown
	return "white";
}

function require_template($id) {
	// sanity checking for security
	$id = str_replace(".", "", $id);
	$id = str_replace("/", "", $id);
	$id = str_replace("\\", "", $id);

	$config = get_site_config();
	if (isset($config["custom_" . $id]) && get_site_config("custom_" . $id)) {
		require(get_site_config("custom_" . $id));
	} else {
		require(__DIR__ . "/../templates/" . $id . ".php");
	}
}
