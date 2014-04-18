<?php

require_once(__DIR__ . "/../inc/global.php");

class DbTest extends PHPUnit_Framework_TestCase {

	function test_db_is_write_query() {
		$queries = array(
			'insert into foo set bar=?',
			'delete from foo where id=1',
			'delete from foo where id in (select id from bar)',
			"delete\nfrom foo",
			"insert\ninto foo values(null)",
			"\ninsert into foo values(null)",
			"UPDATE jobs SET meow=?",
		);
		foreach ($queries as $q) {
			$this->assertTrue(ReplicatedDbWrapper::isWriteQuery($q), "'$q' should be a write query");
		}
	}

	function test_db_is_not_write_query() {
		$queries = array(
			"SELECT * FROM users",
			"SELECT * FROM users WHERE id=?",
			"select * from users",
			"SELECT * FROM users_update WHERE id=?",
			"SELECT * FROM users_insert WHERE delete_id=? OR insert_update=?",
			"SELECT * FROM users_delete WHERE update_id=?",
			"select * from users where id in (select id from bar)",
			"select * from users join x",
			"select\n* from users",
			"\nselect * from users",
			// these queries should explicitly be db_master() if the master is necessary
			"show global status",
			"show slave status",
			"SELECT meow FROM jobs",
		);
		foreach ($queries as $q) {
			$this->assertFalse(ReplicatedDbWrapper::isWriteQuery($q), "'$q' should not be a write query");
		}
	}

	function test_db_switch() {
		$this->assertTrue(get_site_config('database_slave'), "database_slave needs to be true");
		$q = db()->prepare("UPDATE jobs SET meow=?");
		$this->assertTrue($q->isMaster(), "write query should be master");
		$this->assertFalse($q->isSlave(), "write query should not be slave");
		$this->assertTrue(db()->isMaster());
		$this->assertFalse(db()->isSlave());
		$q = db()->prepare("SELECT meow FROM jobs");
		$this->assertTrue($q->isSlave(), "read query should be slave");
		$this->assertFalse($q->isMaster(), "read query should not be master");
		$this->assertTrue(db()->isSlave());
		$this->assertFalse(db()->isMaster());
	}

	function test_equality_sanity() {
		$this->assertTrue(get_site_config('database_slave'), "database_slave needs to be true");
		$this->assertSame(db_master(), db_master());
		$this->assertSame(db_slave(), db_slave());
	}

}
