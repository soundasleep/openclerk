<?php

/**
 * Various cleanup jobs.
 */

// find very old outstanding payments that have not been paid
$q1 = db()->prepare("SELECT * FROM outstanding_premiums WHERE is_paid=0 AND is_unpaid=0 AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
$q1->execute();
while ($outstanding = $q1->fetch()) {
	// delete address balances
	$q = db()->prepare("DELETE FROM address_balances WHERE address_id=?");
	$q->execute(array($outstanding['address_id']));

	// delete address
	$q = db()->prepare("DELETE FROM addresses WHERE id=?");
	$q->execute(array($outstanding['address_id']));

	// mark it as unpaid
	$q = db()->prepare("UPDATE outstanding_premiums SET is_unpaid=1 WHERE id=?");
	$q->execute(array($outstanding['id']));
	crypto_log("Marked outstanding payment " . $outstanding['id'] . " as unpaid, and removed address from system");
}
