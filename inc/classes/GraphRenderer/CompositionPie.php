<?php

/**
 *
 */
class GraphRenderer_CompositionPie extends GraphRenderer {

  var $currency;

  public function __construct($currency) {
    parent::__construct();
    $this->currency = $currency;
  }

  public function requiresUser() {
    return true;
  }

  function usesSummaries() {
    return true;
  }

  public function getTitle() {
    return ct("Total :currency");
  }

  public function getTitleArgs() {
    return array(
      ':currency' => get_currency_abbr($this->currency),
    );
  }

  public function canHaveTechnicals() {
    // do not try to calculate technicals; this also resorts the data by first key
    return false;
  }

  public function getChartType() {
    return "piechart";
  }

  function usesDays() {
    return false;
  }

  function sort_by_balance_desc($a, $b) {
    // requires an int
    if ($a['balance'] == $b['balance'])
      return 0;
    return $a['balance'] < $b['balance'] ? 1 : -1;
  }

  public function getData($days) {

    $key_column = array('type' => 'string', 'title' => ct("Currency"));
    $columns = array();

    // get data
    // TODO could probably cache this
    $q = db()->prepare("SELECT SUM(balance) AS balance, exchange, MAX(created_at) AS created_at FROM balances WHERE user_id=? AND is_recent=1 AND currency=? GROUP BY exchange");
    $q->execute(array($this->getUser(), $this->currency));
    $balances = $q->fetchAll();

    // need to also get address balances
    $summary_balances = get_all_summary_instances($this->getUser());

    // get additional balances
    $data = array();
    if (isset($summary_balances['blockchain' . $this->currency]) && $summary_balances['blockchain' . $this->currency]['balance'] != 0) {
      $balances[] = array(
        "balance" => $summary_balances['blockchain' . $this->currency]['balance'],
        "exchange" => "blockchain",
        "created_at" => $summary_balances['blockchain' . $this->currency]['created_at'],
      );
    }
    if (isset($summary_balances['offsets' . $this->currency]) && $summary_balances['offsets' . $this->currency]['balance'] != 0) {
      $balances[] = array(
        "balance" => $summary_balances['offsets' . $this->currency]['balance'],
        "exchange" => "offsets",
        "created_at" => $summary_balances['offsets' . $this->currency]['created_at'],
      );
    }

    // sort by balance
    usort($balances, array($this, 'sort_by_balance_desc'));

    $last_updated = find_latest_created_at($balances);

    // apply demo_scale and calculate total summary
    $data = array();
    $total = 0;
    foreach ($balances as $b) {
      if ($b['balance'] != 0) {
        $columns[] = array(
          'type' => 'number',
          'title' => get_exchange_name($b['exchange']),
        );
        $data[] = demo_scale($b['balance']);
        $total += demo_scale($b['balance']);
      }
    }

    // return a more helpful message if there is no data
    if (!$data) {
      throw new NoDataGraphException_AddAccountsAddresses();
    }

    // sort data by balance
    $data = array(get_currency_abbr($this->currency) => $data);

    return array(
      'key' => $key_column,
      'columns' => $columns,
      'data' => $data,
      'last_updated' => $last_updated,
    );

  }

}
