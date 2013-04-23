<?php

require("inc/global.php");
require_login();

require("layout/templates.php");
page_header("Your Profile", "page_profile");

$user = get_user(user_id());
if (!$user) {
	throw new Exception("Could not find self user.");
}

?>
<h1>Your Profile</h1>

<p>
<pre>
<?php print_r($user); ?>
</pre>
</p>

<?php
page_footer();
