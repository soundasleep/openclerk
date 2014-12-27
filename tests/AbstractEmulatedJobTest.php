<?php

// mock methods
function crypto_log($m) {
	if (!defined('NO_OUTPUT')) {
		echo "<!-- " . $m . " -->\n";
	}
}
class JobException extends Exception { }

/**
 * Allows us to test jobs directly, by inserting new users into the database
 * as necessary. This job also allows one to set rates for testing, which are
 * then emulated through {@link set_latest_ticker()}, so we can have consistent
 * testing behaviour.
 *
 * @see #user
 * @see #getRates()
 */
abstract class AbstractEmulatedJobTest extends PHPUnit_Framework_TestCase {

	var $user;

  /**
   * Create a new user, and set up the mock mailer.
   */
	function setUp() {
		// first create a new user
		$this->user = $this->createNewUser();

    \Emails\Email::setMockMailer(array($this, "mockMailer"));
	}

  /**
   * Remove the test user, and remove the mock mailer.
   */
	function tearDown() {
		// finally, delete everything related to this user
		$this->deleteUser($this->user);

    \Emails\Email::setMockMailer(null);
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

	/**
	 * Return a list of exchange rates, (exchange, currency1, currency2, last_trade, ask, bid).
	 * Used in {@link #executeJob()}.
	 * @see #executeJob()
	 */
	abstract function getRates();

	var $mails;
	function mockMailer($to_email, $to_name, $subject, $template, $html_template) {
		$this->mails[] = $template;
	}

	function getMails() {
		return $this->mails;
	}

	/**
	 * Return the job type to execute, e.g. 'sum' or 'notification'
	 * @see #executeJob()
	 */
	abstract function getJobType();

	/**
	 * Execute the given job with the given argument ID.
	 * Mocks away the mailer, so mails can be retrieved through {@link #getMails()}.
	 * Does not return anything.
	 *
	 * @see #getMails()
	 * @see #getJobType()
	 */
	function executeJob($user, $arg_id) {
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
		$q->execute(array($user['id'], $this->getJobType(), $arg_id));
		$job_id = db()->lastInsertId();
		$q = db()->prepare("SELECT * FROM jobs WHERE id=?");
		$q->execute(array($job_id));
		$job = $q->fetch();

		require_once(__DIR__ . "/../batch/_batch_insert.php");
		require(__DIR__ . "/../jobs/" . $this->getJobType() . ".php");

		// reset the mailer
		unset($__mock_mailer);
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

		$q = db()->prepare("DELETE FROM summaries WHERE user_id=?");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM summary_instances WHERE user_id=?");
		$q->execute(array($user['id']));

		$q = db()->prepare("DELETE FROM balances WHERE user_id=?");
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
