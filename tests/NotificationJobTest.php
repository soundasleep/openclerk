<?php

require_once(__DIR__ . "/../inc/global.php");

/**
 * Issue #259: tests to check the output of notification jobs.
 */
class NotificationJobTest extends PHPUnit_Framework_TestCase {

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
	 * Test a USD/BTC exchange increase.
	 */
	function testDogecoinExchange() {
		// create a notification
		$id = $this->createNotificationTicker($this->user, array(
				'exchange' => get_default_currency_exchange('usd'),
				'currency1' => 'usd',
				'currency2' => 'btc',
			));
		$arg_id = $this->createNotification($this->user, array(
				'last_value' => 90,
				'notification_type' => 'ticker',
				'type_id' => $id,
				'trigger_condition' => 'increases',
				'trigger_value' => 1,
				'is_percent' => 0
			));

		// execute the job
		$mails = $this->executeJob($this->user, array('btc', 'usd'), $arg_id);
		$this->assertEquals(1, count($mails));
		$exchange_name = get_exchange_name(get_default_currency_exchange('usd'));
		$this->assertHasLine("The exchange rate on $exchange_name for USD/BTC has increased, from 90 USD/BTC to 100 USD/BTC (11.111%), in the last hour.", $mails[0]);

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

	function createNotificationTicker($user, $parameters) {
		$user['user_id'] = $user['id'];
		$q = db()->prepare("INSERT INTO notifications_ticker SET
				exchange=:exchange,
				currency1=:currency1,
				currency2=:currency2");
		$q->execute($parameters);
		return db()->lastInsertId();
	}

	function createNotification($user, $parameters) {
		$parameters['user_id'] = $user['id'];
		$parameters['period'] = 'hour';	// default
		$q = db()->prepare("INSERT INTO notifications SET
				user_id=:user_id,
				last_value=:last_value,
				notification_type=:notification_type,
				type_id=:type_id,
				trigger_condition=:trigger_condition,
				trigger_value=:trigger_value,
				is_percent=:is_percent,
				period=:period");
		$q->execute($parameters);
		return db()->lastInsertId();
	}

	function executeJob($user, $currencies, $arg_id) {
		// insert in the mock rates
		$rates = $this->getRates();

		// by using _latest_ticker we get free mocking, we don't have to insert
		// things into the database, and we don't have to delete them later
		// since they are only local to this scope
		foreach ($rates as $ticker) {
			set_latest_ticker($ticker);
		}

		// now execute the job
		$q = db()->prepare("INSERT INTO jobs SET user_id=?,job_type=?,arg_id=?");
		$q->execute(array($user['id'], 'notification', $arg_id));
		$job_id = db()->lastInsertId();
		$q = db()->prepare("SELECT * FROM jobs WHERE id=?");
		$q->execute(array($job_id));
		$job = $q->fetch();

		global $__mock_mailer;
		$__mock_mailer = array($this, 'mockMailer');

		require_once(__DIR__ . "/../batch/_batch_insert.php");
		require(__DIR__ . "/../jobs/notification.php");

		// return the sent emails
		return $this->mails;
	}

	// $__mock_mailer($to_email, $to_name, $subject, $template);
	var $mails;
	function mockMailer($to_email, $to_name, $subject, $template) {
		$this->mails[] = $template;
	}

	function deleteUser($user) {
		$q = db()->prepare("DELETE FROM users WHERE id=?");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM notifications_ticker WHERE id IN (SELECT type_id FROM notifications WHERE user_id=? AND notification_type='ticker')");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM notifications WHERE user_id=?");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM jobs WHERE user_id=?");
		$q->execute(array($user['id']));

	}

	function assertHasLine($needle, $lines) {
		foreach (explode("\n", $lines) as $line) {
			$line = trim($line);
			if ($line == $needle) {
				return;
			}
		}
		$this->fail("Could not find line '$needle' in '$lines'");
	}

}

// mock methods
function crypto_log($m) {
	if (!defined('NO_OUTPUT')) {
		echo "<!-- " . $m . " -->\n";
	}
}
class JobException extends Exception { }
