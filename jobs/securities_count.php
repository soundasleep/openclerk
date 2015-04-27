<?php

/**
 * Securities count job - count how many securities a user currently has. This value is
 * displayed on the Profile tabs as Your Securities (X).
 */

// construct a query using
$accounts = account_data_grouped();
$unions = array();
$args = array(
	'user_id' => $job['user_id'],
);
$count = 0;
foreach ($accounts['Individual Securities'] as $key => $data) {
	$unique = "u" . $count++;
	$unions[] = "(SELECT :" . $unique . "_name AS exchange, security_id FROM " . $data['table'] . " WHERE user_id=:user_id)\n";	// doesn't directly required an alias
	$args[$unique . '_name'] = $data['exchange'];
}

$query = "SELECT COUNT(*) AS c FROM
	(SELECT exchange, security_id FROM (
		(SELECT exchange, security_id FROM securities WHERE user_id=:user_id AND is_recent=1)
		UNION " . implode(" UNION ", $unions) . "
	) u GROUP BY exchange, security_id) t";
crypto_log("<pre>" . $query . "</pre>");
crypto_log(print_r($args, true));

// execute
$q = db()->prepare($query);
$q->execute($args);
$security_count = $q->fetch();
crypto_log("Securities found for user " . $job['user_id'] . ": " . number_format($security_count['c']));

$q = db()->prepare("UPDATE user_properties SET securities_count=? WHERE id=?");
$q->execute(array($security_count['c'], $job['user_id']));
