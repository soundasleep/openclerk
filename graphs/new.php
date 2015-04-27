<?php

/**
 * How the new graph render code works:
 * {@link render_graph_new()} generates the HTML on the page necessary to render the graph;
 * which uses graphs.js
 * which uses AJAX to load data, and timeouts to reload graphs
 * which then call the site API to load graph data
 * and then renders the graph using Google Graphs API (or whatever graphing framework we're using)
 */

/**
 * Render the HTML on the page necessary for rendering a graph to the user.
 *
 * @param $graph = array(
 *    'graph_type' => $id,
 *    'width' => 8,
 *    'height' => 4,
 *    'page_order' => 0,
 *    'days' => $days,
 *    'id' => 0,
 *    'arg0_resolved' => $name,
 *    'delta' => $delta,
 *    'public' => true,
 *    'no_technicals' => true,
 *  );
 * @param $include_user_hash if true, include user_id and user_hash in the graph data, necessary for
 *        graphs that require user authentication; default is false
 */
function render_graph_new($graph, $include_user_hash = false) {
  global $_rendered_graph_contents;
  if (!$_rendered_graph_contents) {
    // calculate the relevant text for outofdate indicators
    $title = "";
    if (user_logged_in()) {
      $user = get_user(user_id());
      $plural_hours = plural("hour", user_is_new($user) ? get_site_config('refresh_queue_hours_premium') : get_premium_value($user, "refresh_queue_hours"));
      if ($user['is_first_report_sent']) {
        $title = t("This graph will take up to :hours to be updated with recently added or removed accounts.", array(':hours' => $plural_hours));
      } else if ($user['has_added_account']) {
        $title = t("As a new user, it will take up to :hours for this graph to be populated with initial data.", array(':hours' => $plural_hours));
      } else {
        $title = t("You need to add some account data for this graph to display.");
      }
    }

    ?>
    <div id="graph_contents_template" style="display:none;">
      <div class="graph_headings">
        <h1 class="h1"></h1>
        <h2 class="h2"></h2>
        <h2 class="graph_title">
          <a href=""></a>
        </h2>
        <span class="outofdate" style="display:none;" title="<?php echo htmlspecialchars($title); ?>"></span>
        <span class="subheading"></span>
        <span class="last-updated"></span>
        <ul class="graph_controls">
          <li class="move_up"><a><?php echo ht("Move up"); ?></a></li>
          <li class="move_down"><a><?php echo ht("Move down"); ?></a></li>
          <li class="remove"><a><?php echo ht("Remove"); ?></a></li>
          <li class="edit"><a><?php echo ht("Edit"); ?></a></li>
        </ul>
        <div class="edit_target" style="display:none;">
          <ul class="graph_edit_controls">
            <li class="close"><a><?php echo ht("Close"); ?></a></li>
          </ul>
        </div>
      </div>
      <div class="graph-target"><span class="status_loading"><?php echo ht("Loading..."); ?></span></div>
      <div class="graph_extra extra" style="display:none;"><a href="#"></a></span></div>
      <div class="admin-stats-wrapper hide-admin"><span class="admin-stats render_time"></span></div>
    </div>
    <div id="graph_table_template" class="overflow_wrapper extra-text-container" style="display:none;">
      <table class="standard graph_table">
      </table>
    </div>
    <?php
  }

  if (user_logged_in()) {
    $user = get_user(user_id());
    $graph['can_be_edited'] = !($user['graph_managed_type'] == 'auto' && isset($graph['is_managed']) && $graph['is_managed']);
  }
  if (isset($graph['page_id']) && isset($graph['id'])) {
    $graph['move_up_link'] = url_for('profile', array(
        'page' => $graph['page_id'],
        'move_up' => $graph['id']));
    $graph['move_down_link'] = url_for('profile', array(
        'page' => $graph['page_id'],
        'move_down' => $graph['id']));
    $graph['remove_link'] = url_for('profile', array(
        'page' => $graph['page_id'],
        'remove' => $graph['id']));
  }

  if (isset($graph['id']) && $graph['id']) {
    $graph_id = "graph_" . $graph['id'];
  } else {
    $graph_id = "graph_" . rand(0,0xffff);
  }
  $graph['target'] = $graph_id;

  $graph['graphWidth'] = get_site_config('default_graph_width') * $graph['width'];
  $graph['computedWidth'] = $graph['graphWidth'];
  $graph['graphHeight'] = get_site_config('default_graph_height') * $graph['height'];
  $graph['computedHeight'] = $graph['graphHeight'] + 30;

  // if we are logged in, also provide the user ID and computed user hash, to verify that we can
  // correctly access this graph (also means that we don't have to initialise sessions on the API)
  if ($include_user_hash && user_logged_in()) {
    $graph['user_id'] = user_id();
    $graph['user_hash'] = compute_user_graph_hash(get_user(user_id()));
  }

  // enable demo if necessary
  if (require_get("demo", false)) {
    $graph['demo'] = true;
  }

  // we set the widths and heights initially here so that the page layout doesn't move around
  // a lot as the graphs are loaded via AJAX
  $inline_styles = "overflow: hidden; width: " . $graph['computedWidth'] . "px; height: " . $graph['computedHeight'] . "px;";

  switch ($graph['graph_type']) {
    case "linebreak":
    case "heading":
      // don't render anything! this rendering is handled by profile.php
      return;

    case "calculator":
      // a special case for the Calculator widget; it doesn't seem a good idea to
      // have this as an API call that returns a mixture of HTML and Javascript
      ?>
      <div id="<?php echo htmlspecialchars($graph_id); ?>" class="graph graph_calculator" style="<?php echo $inline_styles; ?>">
        <div class="graph_headings">
          <h2 class="graph_title"><?php echo ht("Currency converter"); ?></h2>
        </div>
        <div class="graph-target">
          <?php
          require(__DIR__ . "/../pages/_calculator.php");
          ?>
        </div>
      </div>
      <script type="text/javascript">
      $(document).ready(function() {
        Graphs.render(<?php echo json_encode($graph); ?>, true /* static graph */);
        initialise_calculator($("#<?php echo htmlspecialchars($graph_id); ?>"))
      });
      </script>
      <?php
      return;
  }

  // 'overflow: hidden;' is to fix a Chrome rendering bug
  ?>
    <div id="<?php echo htmlspecialchars($graph_id); ?>" class="graph" style="<?php echo $inline_styles; ?>"></div>
    <script type="text/javascript">
      Graphs.render(<?php echo json_encode($graph); ?>);
    </script>
  <?php
}

$_rendered_graph_contents = false;

class NoGraphRendererException extends GraphException { }
class RenderGraphException extends GraphException { }

class NoDataGraphException_AddAccountsAddresses extends GraphException { }
class NoDataGraphException_AddCurrencies extends GraphException { }

function compute_user_graph_hash($user) {
  return md5(get_site_config('user_graph_hash_salt') . ":" . $user['id'] . ":" . $_SESSION["user_key"]);
}

function has_expected_user_graph_hash($hash, $user) {
  $q = db()->prepare("SELECT * FROM valid_user_keys WHERE user_id=?");
  $q->execute(array($user['id']));
  while ($key = $q->fetch()) {
    if ($hash === md5(get_site_config('user_graph_hash_salt') . ":" . $user['id'] . ":" . $key['user_key'])) {
      return true;
    }
  }
  return false;
}

/**
 * Out of the colour indexes, what should technicals start colouring as?
 * e.g. don't use index 0 or 1 since these may be used for bid/ask prices
 */
function default_technical_colour_index() {
  return 2;
}

/**
 * Helper function that converts a {@code graph_type} to a GraphRenderer
 * object, which we can then use to get raw graph data and format it as necessary.
 */
function construct_graph_renderer($graph_type, $arg0, $arg0_resolved) {
  $bits = explode("_", $graph_type);
  $all_exchanges = get_all_exchanges();
  if (count($bits) == 3) {
    $cur1 = false;
    $cur2 = false;
    if (strlen($bits[1]) == 6) {
      $cur1 = substr($bits[1], 0, 3);
      $cur2 = substr($bits[1], 3);
      $cur1 = in_array($cur1, get_all_currencies()) ? $cur1 : false;
      $cur2 = in_array($cur2, get_all_currencies()) ? $cur2 : false;
    }
    if (strlen($bits[2]) == 6 && !$cur1 && !$cur2) {
      $cur1 = substr($bits[2], 0, 3);
      $cur2 = substr($bits[2], 3);
      $cur1 = in_array($cur1, get_all_currencies()) ? $cur1 : false;
      $cur2 = in_array($cur2, get_all_currencies()) ? $cur2 : false;
    }

    if ($bits[2] == "daily" && $cur1 && $cur2 && isset($all_exchanges[$bits[0]])) {
      return new GraphRenderer_Ticker($bits[0], $cur1, $cur2);
    }

    if ($bits[2] == "markets" && $cur1 && $cur2 && $bits[0] == "average") {
      return new GraphRenderer_AverageMarketData($cur1, $cur2);
    }

    if ($bits[0] == "composition" && in_array($bits[1], get_all_currencies())) {
      switch ($bits[2]) {
        case "pie":
          return new GraphRenderer_CompositionPie($bits[1]);
        case "table":
          return new GraphRenderer_CompositionTable($bits[1]);
        case "daily":
          return new GraphRenderer_CompositionGraph($bits[1]);
        case "stacked":
          return new GraphRenderer_CompositionStacked($bits[1]);
        case "proportional":
          return new GraphRenderer_CompositionProportional($bits[1]);
      }
    }

    if ($bits[0] == "total" && in_array($bits[1], get_all_currencies()) && $bits[2] == "daily") {
      return new GraphRenderer_SummaryGraph('total' . $bits[1], $bits[1]);
    }

    if ($bits[0] == "hashrate" && in_array($bits[1], get_all_currencies()) && $bits[2] == "daily") {
      return new GraphRenderer_SummaryGraphHashrate('totalmh_' . $bits[1], $bits[1]);
    }

    if ($bits[0] == "pair" && isset($all_exchanges[$bits[1]]) && $cur1 && $cur2) {
      return new GraphRenderer_ExchangePair($bits[1], $cur1, $cur2);
    }
  }

  // issue #273: fix bitmarket_pl exchange
  if (count($bits) == 4) {
    $cur1 = false;
    $cur2 = false;
    if (strlen($bits[2]) == 6) {
      $cur1 = substr($bits[2], 0, 3);
      $cur2 = substr($bits[2], 3);
      $cur1 = in_array($cur1, get_all_currencies()) ? $cur1 : false;
      $cur2 = in_array($cur2, get_all_currencies()) ? $cur2 : false;
    }
    if ($bits[3] == "daily" && $cur1 && $cur2 && isset($all_exchanges[$bits[0] . "_" . $bits[1]])) {
      return new GraphRenderer_Ticker($bits[0] . "_" . $bits[1], $cur1, $cur2);
    }
  }

  if (count($bits) >= 2) {
    if (substr($bits[0], 0, strlen("all2")) == "all2" || substr($bits[0], 0, strlen("crypto2")) == "crypto2") {
      $cur = substr($bits[0], -3);
      if (in_array($cur, get_all_currencies())) {
        if (count($bits) == 3 && $bits[2] == "daily" && isset($all_exchanges[$bits[1]])) {
          // e.g. all2nzd_bitnz_daily
          return new GraphRenderer_SummaryGraphConvertedExchange($bits[0] . "_" . $bits[1], $cur);
        }
        if (count($bits) == 4 && $bits[3] == "daily" && isset($all_exchanges[$bits[1] . "_" . $bits[2]])) {
          // e.g. all2pln_bitmarket_pl_daily (#273)
          return new GraphRenderer_SummaryGraphConvertedExchange($bits[0] . "_" . $bits[1] . "_" . $bits[2], $cur);
        }
        if (count($bits) == 2 && $bits[1] == "daily") {
          // e.g. crypto2ltc_daily
          return new GraphRenderer_SummaryGraphConvertedCrypto($bits[0], $cur);
        }
      }
    }

    if ($bits[0] == "securities") {
      $renderer = new GraphRenderer_BalancesGraphSecurities($bits[1], $arg0);
      return $renderer;
    }
  }

  if (count($bits) >= 2 && $bits[0] == "metrics") {
    $possible = GraphRenderer_AdminMetrics::getMetrics();
    $bits_two = explode("_", $graph_type, 2);
    if (isset($possible[$bits_two[1]])) {
      return new GraphRenderer_AdminMetrics($bits_two[1]);
    }
  }

  switch ($graph_type) {
    case "btc_equivalent":
      return new GraphRenderer_EquivalentPieBTC();

    case "balances_table":
      return new GraphRenderer_BalancesTable();

    case "total_converted_table":
      return new GraphRenderer_TotalConvertedTable();

    case "crypto_converted_table":
      return new GraphRenderer_CryptoConvertedTable();

    case "balances_offset_table":
      return new GraphRenderer_BalancesOffsetsTable();

    case "ticker_matrix":
      return new GraphRenderer_TickerMatrix();

    case "btc_equivalent_graph":
      return new GraphRenderer_BtcEquivalentGraph();

    case "btc_equivalent_stacked":
      return new GraphRenderer_BtcEquivalentStacked();

    case "btc_equivalent_proportional":
      return new GraphRenderer_BtcEquivalentProportional();

    case "external_historical":
      return new GraphRenderer_ExternalHistorical($arg0_resolved);

    case "admin_statistics":
      return new GraphRenderer_AdminStatistics();

    case "statistics_queue":
      return new GraphRenderer_StatisticsQueue();

    case "statistics_system_load":
      return new GraphRenderer_StatisticsSystemLoad("");

    case "statistics_db_system_load":
      return new GraphRenderer_StatisticsSystemLoad("db_");

    default:
      throw new NoGraphRendererException("Unknown graph to render '$graph_type'");
  }
}
