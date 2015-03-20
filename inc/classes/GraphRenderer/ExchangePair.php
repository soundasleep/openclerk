<?php

/**
 * Extension from mtgox_btc_table -> pair_mtgox_usdbtc
 * Issue #274
 */
class GraphRenderer_ExchangePair extends GraphRenderer {

  var $exchange;
  var $currency1;
  var $currency2;

  public function __construct($exchange, $currency1, $currency2) {
    parent::__construct();
    $this->exchange = $exchange;
    $this->currency1 = $currency1;
    $this->currency2 = $currency2;
  }

  public function getTitle() {
    return ct(":exchange :pair");
  }

  public function getTitleArgs() {
    return array(
      ':exchange' => get_exchange_name($this->exchange),
      ':pair' => get_currency_abbr($this->currency1) . "/" . get_currency_abbr($this->currency2),
    );
  }

  public function getURL() {
    return url_for('historical', array(
      'id' => $this->exchange . '_' . $this->currency1 . $this->currency2 . '_daily',
      'days' => 180,
    ));
  }

  public function getLabel() {
    return ct("View historical data");
  }

  public function canHaveTechnicals() {
    // do not try to calculate technicals; this also resorts the data by first key
    return false;
  }

  public function getChartType() {
    return "vertical";
  }

  function usesDays() {
    return false;
  }

  public function hasSubheading() {
    // do not try to calculate subheadings!
    return false;
  }

  public function getData($days) {

    $key_column = array('type' => 'string', 'title' => get_exchange_name($this->exchange));

    $columns = array();
    $columns[] = array('type' => 'string', 'title' => ct("Price"), 'heading' => true);
    $columns[] = array('type' => 'string', 'title' => ct("Value"));

    $data = array();

    $q = db()->prepare("SELECT * FROM ticker_recent WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
    $q->execute(array(
      'exchange' => $this->exchange,
      'currency1' => $this->currency1,
      'currency2' => $this->currency2,
    ));
    if ($ticker = $q->fetch()) {
      $last_updated = $ticker['created_at'];
      $data[] = array(
        'Bid',
        currency_format($this->currency1, $ticker['bid'], 4),
      );
      $data[] = array(
        'Ask',
        currency_format($this->currency1, $ticker['ask'], 4),
      );
    } else {
      throw new GraphException(t("No recent rates found for :exchange :pair", $this->getTitleArgs()));
    }

    return array(
      'key' => $key_column,
      'columns' => $columns,
      'data' => $data,
      'last_updated' => $last_updated,

      // do not display header row
      'no_header' => true,
    );

  }

}
