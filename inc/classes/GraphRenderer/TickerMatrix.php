<?php

/**
 * A matrix table of each currency vs. each currency, and their current
 * last_trade and volume on each exchange the user is interested in.
 */
class GraphRenderer_TickerMatrix extends GraphRenderer {

  public function __construct() {
    parent::__construct();
  }

  public function requiresUser() {
    return true;
  }

  public function getTitle() {
    return ct("Total balances");
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

  function getClasses() {
    return "ticker_matrix";
  }

  public function getData($days) {

    $key_column = array('type' => 'string', 'title' => ct("Currency"));
    $columns = array();
    $last_updated = false;

    $columns[] = array('type' => 'string', 'title' => "", 'heading' => true);

    // a matrix table of each currency vs. each currency, and their current
    // last_trade and volume on each exchange the user is interested in
    $currencies = get_all_currencies();
    $summaries = get_all_summary_currencies($this->getUser());
    $conversion = get_all_conversion_currencies($this->getUser());

    $graph["last_updated"] = 0;
    $interested = array();
    foreach ($currencies as $c) {
      if (isset($summaries[$c])) {
        $interested[] = $c;
        $columns[] = array(
          'type' => 'string',
          'title' => get_currency_abbr($c),
        );
      }
    }

    foreach ($interested as $c1) {
      $row = array(get_currency_abbr($c1));
      foreach ($interested as $c2) {
        // go through each exchange pair
        $cell = "";
        foreach (get_exchange_pairs() as $exchange => $pairs) {
          foreach ($pairs as $pair) {
            if ($c1 == $pair[0] && $c2 == $pair[1]) {
              $q = db()->prepare("SELECT * FROM ticker_recent WHERE exchange=? AND currency1=? AND currency2=? LIMIT 1");
              $q->execute(array($exchange, $c1, $c2));
              if ($ticker = $q->fetch()) {
                // TODO currency_format should be a graph option
                $exchange_short = strlen($exchange) > 8 ? substr($exchange, 0, 7) . "..." : $exchange;
                $cell .= "<li><span class=\"rate\">" . number_format_html($ticker['last_trade'], 4) . "</span> " . ($ticker['volume'] == 0 ? "" : "<span class=\"volume\">(" . number_format_html($ticker['volume'], 4) . ")</span>");
                $cell .= " <span class=\"exchange\" title=\"" . htmlspecialchars(get_exchange_name($exchange)) . "\">[" . htmlspecialchars($exchange_short) . "]</span>";
                $cell .= "</li>\n";
                $last_updated = max($last_updated, strtotime($ticker['created_at']));
              } else {
                $cell .= "<li class=\"warning\">" . t("Could not find rate for :exchange: :pair", array(':exchange' => $exchange, ':pair' => $c1 . "/" . $c2)) . "</li>\n";
              }
            }
          }
        }
        if ($cell) {
          $cell = "<ul class=\"rate_matrix\">" . $cell . "</ul>";
        }
        $row[] = $cell;
      }
      $data[] = $row;
    }

    // now delete any empty rows or columns
    // columns
    $deleteRows = array();
    $deleteColumns = array();
    for ($i = 0; $i < count($data) - 1; $i++) {
      $empty = true;
      for ($j = 1; $j < count($data[$i]); $j++) {
        if ($data[$i][$j]) {
          $empty = false;
          break;
        }
      }
      if ($empty) $deleteRows[] = $i;
    }
    for ($i = 1; $i < count($data); $i++) {
      $empty = true;
      for ($j = 0; $j < count($data[$i]) - 1; $j++) {
        if ($data[$j][$i]) {
          $empty = false;
          break;
        }
      }
      if ($empty) $deleteColumns[] = $i;
    }

    $new_data = array();
    foreach ($data as $i => $row) {
      if (in_array($i, $deleteRows)) continue;
      $x = array();
      foreach ($data[$i] as $j => $cell) {
        if (in_array($j, $deleteColumns)) continue;
        $x[] = $cell;
      }
      $new_data[] = $x;
    }
    foreach ($deleteColumns as $i) {
      unset($columns[$i]);
    }
    $columns = array_values($columns);

    return array(
      'key' => $key_column,
      'columns' => $columns,
      'data' => $new_data,
      'last_updated' => $last_updated,

      // display 'add more currencies' text
      'add_more_currencies' => true,
    );

  }

}
