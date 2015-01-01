<?php

/**
 *
 */
class GraphRenderer_BalancesOffsetsTable extends GraphRenderer {

  public function __construct() {
    parent::__construct();
  }

  public function requiresUser() {
    return true;
  }

  public function getTitle() {
    return ct("Total balances");
  }

  function usesSummaries() {
    return true;
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

    $key_column = array('type' => 'string', 'title' => ct("Currency"));
    $columns = array();

    $columns[] = array('type' => 'string', 'title' => ct("Currency"), 'heading' => true);
    $columns[] = array('type' => 'string', 'title' => ct("Balance"));
    $columns[] = array('type' => 'string', 'title' => ct("Offset"));
    $columns[] = array('type' => 'string', 'title' => ct("Total"));

    // a table of each currency, along with an offset field
    $balances = get_all_summary_instances($this->getUser());
    $summaries = get_all_summary_currencies($this->getUser());
    $currencies = get_all_currencies();
    $last_updated = find_latest_created_at($balances, "total");

    // create data
    $data = array();
    foreach ($currencies as $c) {
      if (isset($summaries[$c])) {
        $balance = demo_scale(isset($balances['total'.$c]) ? $balances['total'.$c]['balance'] : 0);
        $offset = demo_scale(isset($balances['offsets'.$c]) ? $balances['offsets'.$c]['balance'] : 0);
        $data[] = array(
          "<span title=\"" . htmlspecialchars(get_currency_name($c)) . "\">" . get_currency_abbr($c) . "</span>",
          currency_format($c, $balance - $offset, 4),
          currency_format($c, $offset, 4),
          currency_format($c, $balance /* balance includes offset */, 4),
        );
      }
    }

    return array(
      'key' => $key_column,
      'columns' => $columns,
      'data' => $data,
      'last_updated' => $last_updated,

      // display 'add more currencies' text
      'add_more_currencies' => true,
    );

  }

}
