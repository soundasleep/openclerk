<?php

define('FORCE_NO_RELATIVE', true);		// url_for() references need to be relative to the base path, not the js/ directory that this script is within

require(__DIR__ . "/../../../inc/content_type/json.php");		// to allow for appropriate headers etc
require(__DIR__ . "/../../../inc/global.php");
require(__DIR__ . "/../../../inc/cache.php");

function api_v1_graphs($graph) {
	$result = array();

	/**
	 * Graph rendering goes like this:
	 * 1. get raw graph data (from a {@link GraphRenderer} through {@link construct_graph_renderer()})
	 * 2. apply deltas as necessary
	 * 3. add technicals as necessary
	 * 4. strip dates outside of the requested ?days parameter (e.g. from extra_days)
	 * 5. construct heading and links
	 * 6. construct subheading and revise last_updated
	 * 7. return data
	 * that is, deltas and technicals are done on the server-side; not the client-side.
	 */
	$renderer = construct_graph_renderer($graph['graph_type']);
	$data = $renderer->getData($graph['days']);
	$original_count = count($data['data']);

	// 4. discard early data
	$data['data'] = discard_early_data($data['data'], $graph['days']);
	$after_discard_count = count($data['data']);

	$result['columns'] = $data['columns'];
	$result['data'] = $data['data'];

	$result['type'] = 'linechart';

	$result['heading'] = array(
		'label' => $renderer->getTitle(),
		'url' => $renderer->getURL(),
		'title' => $renderer->getLabel(),
	);

	// 5. construct subheading and revise last_updated
	$result['subheading'] = format_subheading_values_objects($graph, $data['data'], $data['columns']);
	$result['lastUpdated'] = recent_format_html($data['last_updated']);

	$result['timestamp'] = iso_date();
	$result['success'] = true;

	$result['_debug'] = $graph;
	$result['_debug']['data_discarded'] = $original_count - $after_discard_count;

	return json_encode($result);
}

require(__DIR__ . "/../../../graphs/util.php");
require(__DIR__ . "/../../../graphs/render.php");
require(__DIR__ . "/../../../layout/templates.php");

$graph_type = require_get("graph_type");

$config = array(
	'days' => require_get("days"),
	'delta' => require_get("delta"),
	'arg0' => require_get('arg0'),
	'arg0_resolved' => require_get('arg0_resolved'),
	// TODO technicals, which will need to be part of the hash too
);
$hash = substr(implode(':', $config), 0, 32);
$config['graph_type'] = require_get('graph_type');

// TODO limit 'days' parameter as necessary

$seconds = 1;
allow_cache($seconds);		// allow local cache for up to 60 seconds
echo compile_cached('api/rates/' . $graph_type, $hash /* hash */, $seconds /* cached up to seconds */, 'api_v1_graphs', array($config));

performance_metrics_page_end();

/**
 * Helper function that converts a {@code graph_type} to a GraphRenderer
 * object, which we can then use to get raw graph data and format it as necessary.
 */
function construct_graph_renderer($graph_type) {
	$bits = explode("_", $graph_type);
	if (count($bits) == 3) {
		$all_exchanges = get_all_exchanges();
		if ($bits[2] == "daily" && strlen($bits[1]) == 6 && isset($all_exchanges[$bits[0]])) {
			$cur1 = substr($bits[1], 0, 3);
			$cur2 = substr($bits[1], 3);
			if (in_array($cur1, get_all_currencies()) && in_array($cur2, get_all_currencies())) {
				return new GraphRenderer_Ticker($bits[0], $cur1, $cur2);
			}
		}
	}

	switch ($graph_type) {
		default:
			throw new GraphException("Unknown graph to render '$graph_type'");
	}
}

abstract class GraphRenderer {

	/**
	 * @return an array of (columns => [column], data => [(date, value)], last_updated => (date or false))
	 */
	abstract function getData($days);

	/**
	 * Get the title of this graph
	 */
	abstract function getTitle();

	/**
	 * Get the URL that the title of this graph should link to, or {@code false} if it
	 * should not link anywhere
	 */
	function getURL() {
		return false;
	}

	/**
	 * Get the label that should be associated with the {@link #getURL()}, or
	 * {@code false} if there shouldn't be any.
	 * Should be wrapped in {@link ct()}.
	 */
	function getLabel() {
		return false;
	}

}

/**
 * Helper function to mark strings that need to be translated on the client-side.
 */
function ct($s) {
	return $s;
}

class GraphRenderer_Ticker extends GraphRenderer {

	var $exchange;
	var $currency1;
	var $currency2;

	public function __construct($exchange, $currency1, $currency2) {
		$this->exchange = $exchange;
		$this->currency1 = $currency1;
		$this->currency2 = $currency2;
	}

	public function getTitle() {
		return get_exchange_name($this->exchange) . " " . get_currency_abbr($this->currency1) . "/" . get_currency_abbr($this->currency2);
	}

	public function getURL() {
		return url_for('historical', array('id' => $this->exchange . '_' . $this->currency1 . $this->currency2 . '_daily'));
	}

	public function getLabel() {
		return ct("View historical data");
	}

	public function getData($days) {
		$columns = array();

		$columns[] = array('type' => 'date', 'title' => ct("Date"));
		$columns[] = array('type' => 'number', 'title' => ct(":pair Bid"), 'args' => array('pair' => get_currency_abbr($this->currency1) . "/" . get_currency_abbr($this->currency2)));
		$columns[] = array('type' => 'number', 'title' => ct(":pair Ask"), 'args' => array('pair' => get_currency_abbr($this->currency1) . "/" . get_currency_abbr($this->currency2)));

		if ($this->exchange == 'themoneyconverter' || $this->exchange == "coinbase") {
			// hack fix because TheMoneyConverter and Coinbase only have last_trade
			// TODO this should maybe be in a separate class, e.g. BidAskTicker and LastTradeTicker
			throw new GraphException("Cannot support themoneyconverter or coinbase yet");
		}

		// TODO extra_days_necessary
		$extra_days = 10;

		$sources = array(
			// cannot use 'LIMIT :limit'; PDO escapes :limit into string, MySQL cannot handle or cast string LIMITs
			// first get summarised data
			array('query' => "SELECT * FROM graph_data_ticker WHERE exchange=:exchange AND
				currency1=:currency1 AND currency2=:currency2 AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date'),
			// and then get more recent data
			array('query' => "SELECT * FROM ticker WHERE is_daily_data=1 AND exchange=:exchange AND
				currency1=:currency1 AND currency2=:currency2 ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
		);

		$args = array(
			'exchange' => $this->exchange,
			'currency1' => $this->currency1,
			'currency2' => $this->currency2,
		);

		$data = array();
		$last_updated = false;
		foreach ($sources as $source) {
			$q = db()->prepare($source['query']);
			$q->execute($args);
			while ($ticker = $q->fetch()) {
				$data_key = date('Y-m-d', strtotime($ticker[$source['key']]));
				$data[$data_key] = array(
					(double) graph_number_format($ticker['bid']),
					(double) graph_number_format($ticker['ask']),
				);
				$last_updated = max($last_updated, strtotime($ticker['created_at']));
			}
		}

		// sort by key, but we only want values
		uksort($data, 'cmp_time_reverse');

		return array(
			'columns' => $columns,
			'data' => $data,
			'last_updated' => $last_updated,
		);

	}

}
