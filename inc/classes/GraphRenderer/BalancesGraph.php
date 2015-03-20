<?php

class GraphRenderer_BalancesGraph extends GraphRenderer_AbstractTicker {

  var $exchange;
  var $account_id;
  var $currency;

  public function __construct($exchange, $account_id, $currency) {
    parent::__construct();
    $this->exchange = $exchange;
    $this->account_id = $account_id;
    $this->currency = $currency;
  }

  public function requiresUser() {
    return true;
  }

  function usesSummaries() {
    return true;
  }

  public function getTitle() {
    return ct(":exchange");
  }

  public function getTitleArgs() {
    return array(
      ':exchange' => get_exchange_name($this->exchange),
    );
  }

  /**
   * @return an array of columns e.g. (type, title, args)
   */
  function getTickerColumns() {
    $columns = array();
    $columns[] = array('type' => 'number', 'title' => get_currency_abbr($this->currency));
    return $columns;
  }

  /**
   * The sources must return 'created_at' column as well, for last_updated
   * @return an array of queries e.g. (query, key = created_at/data_date)
   */
  function getTickerSources($days, $extra_days) {
    return array(
      // first get summarised data
      array('query' => "SELECT * FROM graph_data_balances WHERE user_id=:user_id AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND
        data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date'),
      // and then get more recent data
      array('query' => "SELECT * FROM balances WHERE is_daily_data=1 AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND
        user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
    );
  }

  /**
   * @return an array of arguments passed to each {@link #getTickerSources()}
   */
  function getTickerArgs() {
    return array(
      'user_id' => $this->getUser(),
      'exchange' => $this->exchange,
      'account_id' => $this->account_id,
      'currency' => $this->currency,
    );
  }

  /**
   * @return an array of 1..2 values of the values for the particular row,
   *      maybe formatted with {@link #graph_number_format()}.
   */
  function getTickerData($row) {
    if (isset($row['balance_closing'])) {
      return array(
        graph_number_format(demo_scale($row['balance_closing'])),
      );
    } else {
      return array(
        graph_number_format(demo_scale($row['balance'])),
      );
    }
  }

}
