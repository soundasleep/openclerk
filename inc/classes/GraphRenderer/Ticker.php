<?php

class GraphRenderer_Ticker extends GraphRenderer_AbstractTicker {

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

  /**
   * @return an array of columns e.g. (type, title, args)
   */
  function getTickerColumns() {

    $args = array(':pair' => get_currency_abbr($this->currency1) . "/" . get_currency_abbr($this->currency2));
    $columns = array();
    if ($this->onlyhasLastTrade()) {
      // hack fix because TheMoneyConverter and Coinbase only have last_trade
      $columns[] = array('type' => 'number', 'title' => ct(":pair"), 'args' => $args);
    } else {
      $columns[] = array('type' => 'number', 'title' => ct(":pair Bid"), 'args' => $args);
      $columns[] = array('type' => 'number', 'title' => ct(":pair Ask"), 'args' => $args);
    }
    return $columns;
  }

  /**
   * The sources must return 'created_at' column as well, for last_updated
   * @return an array of queries e.g. (query, key = created_at/data_date)
   */
  function getTickerSources($days, $extra_days) {
    return array(
      // cannot use 'LIMIT :limit'; PDO escapes :limit into string, MySQL cannot handle or cast string LIMITs
      // first get summarised data
      array('query' => "SELECT * FROM graph_data_ticker WHERE exchange=:exchange AND
        currency1=:currency1 AND currency2=:currency2 AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date'),
      // and then get more recent data
      array('query' => "SELECT * FROM ticker WHERE is_daily_data=1 AND exchange=:exchange AND
        currency1=:currency1 AND currency2=:currency2 ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
    );
  }

  /**
   * @return an array of arguments passed to each {@link #getTickerSources()}
   */
  function getTickerArgs() {
    return array(
      'exchange' => $this->exchange,
      'currency1' => $this->currency1,
      'currency2' => $this->currency2,
    );
  }

  function onlyHasLastTrade() {
    return $this->exchange == 'themoneyconverter' || $this->exchange == "coinbase" || $this->exchange == "cryptsy";
  }

  /**
   * @return an array of 1..2 values of the values for the particular row,
   *      maybe formatted with {@link #graph_number_format()}.
   */
  function getTickerData($row) {
    if ($this->onlyHasLastTrade()) {
      // last_trade is in ticker; last_trade_closing is in graph_data_ticker
      if (isset($row['last_trade'])) {
        return array(graph_number_format($row['last_trade']));
      } else {
        return array(graph_number_format($row['last_trade_closing']));
      }
    } else {
      return array(
        graph_number_format($row['bid']),
        graph_number_format($row['ask']),
      );
    }
  }

}
