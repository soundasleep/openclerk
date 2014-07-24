<?php

require_once(__DIR__ . "/../inc/global.php");
require_once(__DIR__ . "/AbstractEmulatedJobTest.php");

/**
 * Issue #259: tests to check the output of notification jobs.
 */
class NotificationJobTest extends AbstractEmulatedJobTest {

	function getJobType() {
		return "notification";
	}

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
				'exchange' => get_default_currency_exchange('dog'),
				'currency1' => 'btc',
				'currency2' => 'dog',
				'last_trade' => 3e-7,
				'ask' => 3.2e-7,
				'bid' => 2.8e-7,
			),
		);
	}

	/**
	 * Test a USD/BTC exchange increase notification
	 */
	function testIncrease() {
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
		$this->executeJob($this->user, $arg_id);
		$mails = $this->getMails();
		$this->assertEquals(1, count($mails));
		$exchange_name = get_exchange_name(get_default_currency_exchange('usd'));
		$this->assertHasLine("The exchange rate on $exchange_name for USD/BTC has increased, from 90 USD/BTC to 100 USD/BTC (11.111%), in the last hour.", $mails[0]);

	}

	/**
	 * Test a USD/BTC exchange increase notification, above a value
	 */
	function testIncreaseAbove() {
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
				'trigger_condition' => 'above',
				'trigger_value' => 95,
				'is_percent' => 0
			));

		// execute the job
		$this->executeJob($this->user, $arg_id);
		$mails = $this->getMails();
		$this->assertEquals(1, count($mails));
		$exchange_name = get_exchange_name(get_default_currency_exchange('usd'));
		$this->assertHasLine("The exchange rate on $exchange_name for USD/BTC has increased above 95 USD/BTC, from 90 USD/BTC to 100 USD/BTC (11.111%), in the last hour.", $mails[0]);

	}

	/**
	 * Test a USD/BTC exchange increase notification,
	 * if the rate didn't actually increase.
	 */
	function testNop() {
		// create a notification
		$id = $this->createNotificationTicker($this->user, array(
				'exchange' => get_default_currency_exchange('usd'),
				'currency1' => 'usd',
				'currency2' => 'btc',
			));
		$arg_id = $this->createNotification($this->user, array(
				'last_value' => 100,
				'notification_type' => 'ticker',
				'type_id' => $id,
				'trigger_condition' => 'increases',
				'trigger_value' => 1,
				'is_percent' => 0
			));

		// execute the job
		$this->executeJob($this->user, $arg_id);
		$mails = $this->getMails();
		$this->assertEquals(0, count($mails));

	}

	/**
	 * Test a BTC/DOGE exchange increase notification, particularly the precision.
	 * Issue #257
	 */
	function testDogecoin() {
		// create a notification
		$id = $this->createNotificationTicker($this->user, array(
				'exchange' => get_default_currency_exchange('dog'),
				'currency1' => 'btc',
				'currency2' => 'dog',
			));
		$arg_id = $this->createNotification($this->user, array(
				'last_value' => 2e-7,
				'notification_type' => 'ticker',
				'type_id' => $id,
				'trigger_condition' => 'increases',
				'trigger_value' => 1,
				'is_percent' => 0
			));

		// execute the job
		$this->executeJob($this->user, $arg_id);
		$mails = $this->getMails();
		$this->assertEquals(1, count($mails));
		$exchange_name = get_exchange_name(get_default_currency_exchange('dog'));
		$this->assertHasLine("The exchange rate on $exchange_name for BTC/DOGE has increased, from 0.00000020 BTC/DOGE to 0.00000030 BTC/DOGE (50%), in the last hour.", $mails[0]);

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

}
