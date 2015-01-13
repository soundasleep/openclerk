<?php

/**
 * Outstanding premium payments job.
 */

// get the relevant premium and address info
$q = db()->prepare("SELECT p.*,
    a.address, a.currency
  FROM outstanding_premiums AS p
  JOIN addresses AS a ON p.address_id=a.id
  WHERE p.id=?");
$q->execute(array($job['arg_id']));
$address = $q->fetch();
if (!$address) {
  throw new JobException("Cannot find outstanding ID " . $job['arg_id'] . " with a relevant address");
}

$reminder = recent_format(strtotime("+" . get_site_config('outstanding_reminder_hours') . " hour"), false, "");
$cancelled = recent_format(strtotime("+" . get_site_config('outstanding_abandon_days') . " day"), false, "");
crypto_log("Reminders are sent every '$reminder'; cancelled after '$cancelled'.");

// get current user
$q = db()->prepare("SELECT * FROM users WHERE id=?");
$q->execute(array($address['user_id']));
$user = $q->fetch();
if (!$user) {
  throw new JobException("Could not find user " . $address['user_id']);
}

// find the most recent balance
$q = db()->prepare("SELECT * FROM address_balances WHERE address_id=? AND is_recent=1");
$q->execute(array($address['address_id']));
$balance = $q->fetch();
if (!$balance) {
  // no balance yet
  crypto_log("No balance retrieved yet.");

} else {

  if ($address['balance'] == 0) {
    // to prevent div/0 errors
    throw new JobException("Cannot handle an address with balance of zero");
  }

  // is it enough?
  if ($balance['balance'] >= $address['balance']) {
    crypto_log("Sufficient balance found: " . $balance['balance'] . " (expected " . $address['balance'] . "), applying premium status to user " . $address['user_id']);

    // calculate new expiry date
    $expires = max(strtotime($user['premium_expires']), time());
    crypto_log("Old expiry date: " . db_date($expires));

    // in case they paid too much, scale the amount of premium accordingly
    // but need to round it down otherwise strtotime may fail
    $expires = strtotime(db_date($expires) . " +" . max(0, floor($address['months'] * ($balance['balance'] / $address['balance']))) . " months +" . max(0, floor($address['years'] * ($balance['balance'] / $address['balance']))) . " years");
    crypto_log("New premium expiry date: " . db_date($expires));

    // apply premium data to user account
    $q = db()->prepare("UPDATE users SET updated_at=NOW(),is_premium=1, premium_expires=?, is_reminder_sent=0 WHERE id=? LIMIT 1");
    $q->execute(array(db_date($expires), $address['user_id']));

    // update outstanding premium as paid
    $q = db()->prepare("UPDATE outstanding_premiums SET is_paid=1,paid_at=NOW(),paid_balance=? WHERE id=?");
    $q->execute(array($balance['balance'], $address['id']));

    // remove the address from the system account
    // (otherwise we will be continuing to try and check this address forever, even after it's paid)
    $q = db()->prepare("DELETE FROM addresses WHERE id=?");
    $q->execute(array($balance['address_id']));

    $q = db()->prepare("DELETE FROM address_balances WHERE address_id=?");
    $q->execute(array($balance['address_id']));
    crypto_log("Deleted old address and address balances");

    // update jobs to premium priority
    $q = db()->prepare("UPDATE jobs SET priority=? WHERE user_id=? AND is_executed=0 AND priority > ?");
    $q->execute(array(get_site_config('premium_job_priority'), $address['user_id'], get_site_config('premium_job_priority')));
    crypto_log("Updated old jobs to new priority");

    // try sending email, if an email address has been registered
    if ($user['email']) {
      send_user_email($user, "purchase_payment", array(
        "name" => ($user['name'] ? $user['name'] : $user['email']),
        "amount" => number_format_autoprecision($balance['balance']),
        "received" => number_format_autoprecision($balance['balance']),
        "currency" => get_currency_abbr($address['currency']),
        "currency_name" => get_currency_name($address['currency']),
        "expires" => db_date($expires),
        "address" => $address['address'],
        "explorer" => get_explorer_address($address['currency'], $address['address']),
        "url" => absolute_url(url_for("user#user_outstanding")),
        "profile_url" => absolute_url(url_for("user#user_premium")),
        "reminder" => $reminder,
        "cancelled" => $cancelled,
      ));
      crypto_log("Sent e-mail to " . htmlspecialchars($user['email']) . ".");
    }

  } else {
    crypto_log("Insufficient balance found: " . $balance['balance'] . " (expected " . $address['balance'] . ")");

    if (strtotime($address['created_at'] . " +" . get_site_config('outstanding_reminder_hours') . " hour") < time()) {
      if (strtotime($address['created_at'] . " + " . get_site_config('outstanding_abandon_days') . " day") < time()) {
        // abandon the payment
        crypto_log("Payment is more than " . get_site_config('outstanding_abandon_days') . " days old: abandoning");

        // delete address balances (we don't need them anymore)
        $q = db()->prepare("DELETE FROM address_balances WHERE address_id=?");
        $q->execute(array($address['address_id']));

        // delete address (to prevent job queues)
        $q = db()->prepare("DELETE FROM addresses WHERE id=?");
        $q->execute(array($address['address_id']));

        // handle partial payments
        if ($balance['balance'] > 0) {
          // the user has paid *something* towards premium
          crypto_log("User has already paid " . $balance['balance'] . " " . $address['currency'] . ": crediting with partial premium");

          // calculate new expiry date
          $expires = max(strtotime($user['premium_expires']), time());
          crypto_log("Old expiry date: " . db_date($expires));

          // scale the amount of premium accordingly
          // but need to round it down otherwise strtotime may fail
          $expires = strtotime(db_date($expires) . " +" . max(0, floor($address['months'] * ($balance['balance'] / $address['balance']))) . " months +" . max(0, floor($address['years'] * ($balance['balance'] / $address['balance']))) . " years");
          crypto_log("New premium expiry date: " . db_date($expires));

          // apply premium data to user account
          $q = db()->prepare("UPDATE users SET updated_at=NOW(),is_premium=1, premium_expires=?, is_reminder_sent=0 WHERE id=? LIMIT 1");
          $q->execute(array(db_date($expires), $address['user_id']));

          // update outstanding premium as paid
          $q = db()->prepare("UPDATE outstanding_premiums SET is_paid=1,paid_at=NOW(),paid_balance=? WHERE id=?");
          $q->execute(array($balance['balance'], $address['id']));

          // update jobs to premium priority
          $q = db()->prepare("UPDATE jobs SET priority=? WHERE user_id=? AND is_executed=0 AND priority > ?");
          $q->execute(array(get_site_config('premium_job_priority'), $address['user_id'], get_site_config('premium_job_priority')));
          crypto_log("Updated old jobs to new priority");

          if ($user['email']) {
            send_user_email($user, "purchase_partial", array(
              "name" => ($user['name'] ? $user['name'] : $user['email']),
              "amount" => number_format_autoprecision($address['balance']),
              "received" => number_format_autoprecision($balance['balance']),
              "currency" => get_currency_abbr($address['currency']),
              "currency_name" => get_currency_name($address['currency']),
              "expires" => db_date($expires),
              "address" => $address['address'],
              "explorer" => get_explorer_address($address['currency'], $address['address']),
              "url" => absolute_url(url_for("user#user_premium")),
              "profile_url" => absolute_url(url_for("user#user_premium")),
              "reminder" => $reminder,
              "cancelled" => $cancelled,
            ));
            crypto_log("Sent e-mail to " . htmlspecialchars($user['email']) . ".");
          }

        } else {
          // the user hasn't paid a thing

          // mark it as unpaid
          $q = db()->prepare("UPDATE outstanding_premiums SET is_unpaid=1,cancelled_at=NOW() WHERE id=?");
          $q->execute(array($address['id']));

          // release the premium address
          $q = db()->prepare("UPDATE premium_addresses SET is_used=0,used_at=NULL WHERE id=?");
          $q->execute(array($address['premium_address_id']));

          if ($user['email']) {
            send_user_email($user, "purchase_cancelled", array(
              "name" => ($user['name'] ? $user['name'] : $user['email']),
              "amount" => number_format_autoprecision($address['balance']),
              "received" => number_format_autoprecision($balance['balance']),
              "currency" => get_currency_abbr($address['currency']),
              "currency_name" => get_currency_name($address['currency']),
              "address" => $address['address'],
              "explorer" => get_explorer_address($address['currency'], $address['address']),
              "url" => absolute_url(url_for("user#user_premium")),
              "reminder" => $reminder,
              "cancelled" => $cancelled,
            ));
            crypto_log("Sent e-mail to " . htmlspecialchars($user['email']) . ".");
          }

        }

      } else {
        // have we reminded recently?
        if (!$address['last_reminder'] || strtotime($address['last_reminder'] . " +" . get_site_config('outstanding_reminder_hours') . " hour") < time()) {
          // send a reminder
          if ($user['email']) {
            send_user_email($user, "purchase_reminder", array(
              "name" => ($user['name'] ? $user['name'] : $user['email']),
              "amount" => number_format_autoprecision($address['balance']),
              "received" => number_format_autoprecision($balance['balance']),
              "currency" => get_currency_abbr($address['currency']),
              "currency_name" => get_currency_name($address['currency']),
              "address" => $address['address'],
              "explorer" => get_explorer_address($address['currency'], $address['address']),
              "url" => absolute_url(url_for("user#user_outstanding")),
              "reminder" => $reminder,
              "cancelled" => $cancelled,
            ));
            crypto_log("Sent e-mail to " . htmlspecialchars($user['email']) . ".");
          }

          $q = db()->prepare("UPDATE outstanding_premiums SET last_reminder=NOW() WHERE id=?");
          $q->execute(array($address['id']));
          crypto_log("Sent reminder message on outstanding premium payment.");

        }

      }

    } else if ($balance['balance'] > 0 && $balance['balance'] > $address['last_balance']) {
      // issue #231: have we made a new payment since we looked last?

      // send a reminder
      if ($user['email']) {
        send_user_email($user, "purchase_further", array(
          "name" => ($user['name'] ? $user['name'] : $user['email']),
          "amount" => number_format_autoprecision($address['balance']),
          "received" => number_format_autoprecision($balance['balance']),
          "difference" => number_format_autoprecision($balance['balance'] - $address['last_balance']),
          "currency" => get_currency_abbr($address['currency']),
          "currency_name" => get_currency_name($address['currency']),
          "address" => $address['address'],
          "explorer" => get_explorer_address($address['currency'], $address['address']),
          "url" => absolute_url(url_for("user#user_outstanding")),
          "reminder" => $reminder,
          "cancelled" => $cancelled,
        ));
        crypto_log("Sent e-mail to " . htmlspecialchars($user['email']) . ".");
      }

      $q = db()->prepare("UPDATE outstanding_premiums SET last_balance=? WHERE id=?");
      $q->execute(array($balance['balance'], $address['id']));
      crypto_log("Sent received payment message on outstanding premium payment.");
    }

  }
}
