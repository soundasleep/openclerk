<?php

/**
 * Update the total votes and proportional votes for all voted coins.
 */

// first find out the total votes from each user, and if they're premium or not
$user_cache = array();
$q = db()->prepare("SELECT * FROM vote_coins_votes");
$q->execute();
while ($vote = $q->fetch()) {
	if (!isset($user_cache[$vote['user_id']])) {
		$user_cache[$vote['user_id']] = get_user($vote['user_id']);
		$user_cache[$vote['user_id']]['total_votes'] = 0;
	}

	if ($user_cache[$vote['user_id']]) {
		$user_cache[$vote['user_id']]['total_votes']++;
	}
}
crypto_log("Found " . number_format(count($user_cache)) . " users who have voted.");

// get all of the coins that can be voted on
$q = db()->prepare("SELECT * FROM vote_coins");
$q->execute();
$coins = array();
while ($coin = $q->fetch()) {
	$coins[$coin['id']] = $coin;
	$coins[$coin['id']]['total_users'] = 0;
	$coins[$coin['id']]['total_votes'] = 0;
}
crypto_log("Found " . number_format(count($coins)) . " coins to be voted on.");

// then work out the proportional votes for each coin
$q = db()->prepare("SELECT * FROM vote_coins_votes");
$q->execute();
$total_votes = 0;
while ($vote = $q->fetch()) {
	if (isset($user_cache[$vote['user_id']])) {
		$user = $user_cache[$vote['user_id']];
		$coins[$vote['coin_id']]['total_users']++;
		$coins[$vote['coin_id']]['total_votes'] += (1 / $user['total_votes']) * get_site_config('premium_user_votes');
		$total_votes++;
	}
}
crypto_log("Processed " . number_format($total_votes) . " total votes.");

// now we can update all of the coins
foreach ($coins as $coin) {
	$q = db()->prepare("UPDATE vote_coins SET last_updated=NOW(), total_votes=?, total_users=? WHERE id=?");
	$q->execute(array($coin['total_votes'], $coin['total_users'], $coin['id']));
}
crypto_log("Update coin votes.");
