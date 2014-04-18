<?php

require_once(__DIR__ . "/../inc/global.php");

/**
 * Issue #112: tests to make sure that the sum job is considering all currencies correctly.
 * It's pretty important to test this functionality, because it is so critical to the
 * correct operation of the site.
 */
class SumJobTest extends PHPUnit_Framework_TestCase {

	var $user;

	function getRates() {
		return array(
			array(
				'exchange' => get_default_currency_exchange('usd'),
				'currency1' => 'usd',
				'currency2' => 'btc',
				'last_trade' => 100,
				'ask' => 105,
				'bid' => 95,
			),
			array(
				'exchange' => get_default_currency_exchange('eur'),
				'currency1' => 'eur',
				'currency2' => 'btc',
				'last_trade' => 200,
				'ask' => 205,
				'bid' => 195,
			),
		);
	}

	function setUp() {
		// first create a new user
		$this->user = $this->createNewUser();
	}

	function tearDown() {
		// finally, delete everything related to this user
		$this->deleteUser($this->user);
	}

	/**
	 * The user only has BTC and is interested in USD/BTC.
	 */
	function testJustBTC() {

		// create some account balances
		$this->createAccountBalance($this->user, "btce", "btc", 100);

		// do conversions
		$values = $this->executeSum($this->user, array('btc', 'usd'));

		// checks
		$this->assertEqualRate($values, 100, "totalbtc");
		$this->assertEqualRate($values, 0, "totalusd");
		$this->assertEqualRate($values, 100 * 105, "all2usd_" . get_default_currency_exchange('usd'));

	}

	/**
	 * The user only has USD and is interested in USD/BTC.
	 */
	function testJustUSD() {

		// create some account balances
		$this->createAccountBalance($this->user, "btce", "usd", 100);

		// do conversions
		$values = $this->executeSum($this->user, array('btc', 'usd'));

		// checks
		$this->assertEqualRate($values, 0, "totalbtc");
		$this->assertEqualRate($values, 100, "totalusd");
		$this->assertEqualRate($values, 100, "all2usd_" . get_default_currency_exchange('usd'));
		$this->assertEqualRate($values, 100 / 105, "equivalent_btc_usd");

	}

	/**
	 * The user only has EUR and is interested in USD/BTC/EUR.
	 */
	function testJustEUR() {

		// create some account balances
		$this->createAccountBalance($this->user, "btce", "eur", 100);

		// do conversions
		$values = $this->executeSum($this->user, array('btc', 'usd', 'eur'));

		// checks
		$this->assertEqualRate($values, 0, "totalbtc");
		$this->assertEqualRate($values, 0, "totalusd");
		$this->assertEqualRate($values, 100, "totaleur");
		$this->assertEqualRate($values, 100 / 205, "equivalent_btc_eur");
		$this->assertEqualRate($values, 100 / 205 * 95, "all2usd_" . get_default_currency_exchange('usd'));		// issue #112: if not implemented, this will = 0

	}

	function assertEqualRate($values, $expected, $currency) {
		$this->assertTrue(isset($values[$currency]), "No converted [$currency] rate found in [" . print_r($values, true) . "]");
		$delta = $values[$currency] - $expected;
		$this->assertTrue(abs($delta) <= $expected / 1e6, "Expected [$currency] conversion to be [$expected], was [" . $values[$currency] . "]");
	}

	function createNewUser() {
		$q = db()->prepare("INSERT INTO users SET name=:name, email=:email, country=:country, user_ip=:ip, is_first_report_sent=1");
		$q->execute(array(
			'name' => 'Test user ' . date('r'),
			'email' => 'test@openiaml.org',
			'country' => 'NZ',
			'ip' => '',
		));
		$user_id = db()->lastInsertId();

		return get_user($user_id);
	}

	function createAccountBalance($user, $exchange, $currency, $balance) {
		$q = db()->prepare("INSERT INTO balances SET user_id=:user, exchange=:exchange, balance=:balance, currency=:currency, account_id=0, is_recent=1");
		$q->execute(array(
			'user' => $user['id'],
			'exchange' => $exchange,
			'currency' => $currency,
			'balance' => $balance,
		));
	}

	function executeSum($user, $currencies) {
		// insert in the mock rates
		$rates = $this->getRates();

		// by using _latest_ticker we get free mocking, we don't have to insert
		// things into the database, and we don't have to delete them later
		// since they are only local to this scope
		foreach ($rates as $ticker) {
			set_latest_ticker($ticker);
		}

		// insert in summary currencies
		$summary_map = array();
		foreach ($currencies as $cur) {
			$q = db()->prepare("INSERT INTO summaries SET user_id=?, summary_type=?");
			$q->execute(array($user['id'], 'summary_' . $cur . (is_fiat_currency($cur) ? '_' . get_default_currency_exchange($cur) : '')));
		}

		// now execute the job
		$q = db()->prepare("INSERT INTO jobs SET user_id=?,job_type=?");
		$q->execute(array($user['id'], 'sum'));
		$job_id = db()->lastInsertId();
		$q = db()->prepare("SELECT * FROM jobs WHERE id=?");
		$q->execute(array($job_id));
		$job = $q->fetch();

		require_once(__DIR__ . "/../_batch_insert.php");
		require(__DIR__ . "/../jobs/sum.php");

		// now, find all summary_instances
		$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND is_recent=1");
		$q->execute(array($user['id']));
		$result = array();
		while ($si = $q->fetch()) {
			$result[$si['summary_type']] = $si['balance'];
		}
		return $result;
	}

	function deleteUser($user) {
		$q = db()->prepare("DELETE FROM users WHERE id=?");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM summaries WHERE user_id=?");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM summary_instances WHERE user_id=?");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM jobs WHERE user_id=?");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM balances WHERE user_id=?");
		$q->execute(array($user['id']));

	}

}

// mock methods
function crypto_log($m) {
	if (!defined('NO_OUTPUT')) {
		echo "<!-- " . $m . " -->\n";
	}
}
