<?php

function page_header($page_title, $page_id = false, $options = array()) {

	define('PAGE_RENDER_START', microtime(true));
	header('Content-type: text/html; charset=utf-8');

?>
<!DOCTYPE HTML>
<html>
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
    <?php if (isset($options["js"]) && $options["js"]) { ?>
    <script type="text/javascript" src="js/<?php echo htmlspecialchars($options['js']); ?>.js"></script>
    <?php } ?>
</head>
<body<?php if ($page_id) echo ' id="' . $page_id . '"'; ?>>
<div class="body_wrapper">

<div id="navigation">
<ul>
	<li class="home"><a href="<?php echo url_for('index'); ?>">Home</a></li>
	<?php if (user_logged_in()) { ?>
		<li><a href="<?php echo url_for('profile'); ?>">Your Summary</a></li>
		<li><a href="<?php echo url_for('accounts'); ?>">Your Accounts</a></li>
		<li><a href="<?php echo url_for('user'); ?>">User Profile</a></li>
		<li><a href="<?php echo url_for('login', array('logout' => 1)); ?>">Logout</a></li>
		<?php if (is_admin()) { ?>
			<li class="admin"><a href="<?php echo url_for('status'); ?>">System Status</a></li>
		<?php } ?>
	<?php } else { ?>
		<li><a href="<?php echo url_for('signup'); ?>">Signup</a></li>
		<li><a href="<?php echo url_for('login'); ?>">Login</a></li>
	<?php } ?>
	<li><a href="<?php echo url_for('premium'); ?>">Premium</a></li>
	<li><a href="<?php echo url_for('help'); ?>">Help</a></li>
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

<div id="footer_nav">
	<ul class="footer_nav_list">
		<li><span class="title"><?php echo htmlspecialchars(get_site_config('site_name')); ?></span>
			<ul>
				<li><a href="<?php echo htmlspecialchars(url_for('index')); ?>">About</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('premium')); ?>">Get Premium</a></li>
				<li><a href="http://openclerk.org" target="_blank">Openclerk.org</a></li>
			</ul>
		</li>
		<li><span class="title">Your Account</span>
			<ul>
				<?php if (user_logged_in()) { ?>
				<li><a href="<?php echo htmlspecialchars(url_for('user')); ?>">User Profile</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('accounts')); ?>">Your Accounts</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">Your Summaries</a></li>
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
				<li><a href="mailto:<?php echo htmlspecialchars(get_site_config('site_email')); ?>">Contact Us</a></li>
				<li><a href="<?php echo htmlspecialchars(url_for('external')); ?>">External API Status</a></li>
			</ul>
		</li>
	</ul>

	<div id="copyright">
		<?php echo htmlspecialchars(get_site_config('site_name')); ?> &copy; 2013<?php if (date('Y') != 2013) echo "-" . date('Y'); ?>, powered by <a href="http://openclerk.org" target="_blank">openclerk.org</a><br>
	</div>
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

// TODO $precision should be 8, but then we need to change all other instances -1
function currency_format($currency_code, $n, $precision = 9) {
	$currency = strtoupper($currency_code);

	if (!is_numeric($n)) {
		return "<span class=\"error\">" . $n . " $currency</span>";
	}

	// if we have 100.x, we only want $precision = 6
	if ($n > 1) {
		$precision -= (log($n) / log(10) - 1);
	}

	// find the lowest precision that we need
	for ($i = 0; $i < $precision - 1; $i++) {
		if (number_format($n, $i) == $n) {
			$precision = $i;
			break;
		}
	}
	return "<span class=\"" . strtolower($currency) . "_format\" title=\"" . number_format($n, 8) . " $currency\">" . number_format($n, $precision) . " $currency</span>";
}

function require_template($id) {
	// sanity checking for security
	$id = str_replace(".", "", $id);
	$id = str_replace("/", "", $id);
	$id = str_replace("\\", "", $id);

	if (isset(get_site_config()["custom_" . $id]) && get_site_config("custom_" . $id)) {
		require(get_site_config("custom_" . $id));
	} else {
		require("templates/" . $id . ".php");
	}
}
