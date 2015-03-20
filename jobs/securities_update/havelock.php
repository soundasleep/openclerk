<?php

/**
 * A batch script to get all current Havelock securities and queue them up for ticker values,
 * so we will have historical data even if no user has the security yet.
 */

$exchange = "securities_havelock";
$currency = 'btc';

// get the API data
$content = crypto_get_contents(crypto_wrap_url('https://www.havelockinvestments.com/r/tickerfull'));
if (!$content) {
  throw new ExternalAPIException("API returned empty data");
}
$json = json_decode($content, true);
if (!$json) {
  throw new ExternalAPIException("JSON was invalid");
}

foreach ($json as $security => $data) {
  // $data only has last price, so we'll let securities_havelock job deal with the bid/ask
  $q = db()->prepare("SELECT * FROM securities_havelock WHERE name=?");
  $q->execute(array($security));
  $security_def = $q->fetch();
  if (!$security_def) {
    // need to insert a new security definition, so we can later get its value
    // we can't calculate the value of this security yet
    crypto_log("No securities_havelock definition existed for '" . htmlspecialchars($security) . "': adding in new definition");
    $q = db()->prepare("INSERT INTO securities_havelock SET name=?");
    $q->execute(array($security));
    $security_def = array(
      'name' => $security,
      'id' => db()->lastInsertId(),
    );
  }

  $balance = $data['last'];

  // since we already have last price here, we might as well save it for free
  insert_new_balance($job, $security_def, $exchange, $currency, $balance);

}

