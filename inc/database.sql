-- needs MySQL 5.1
-- for MySQL 5.5, you can replace:
--    created_at datetime not null default now()
--    updated_at datetime not null default now() on update now()
-- and remove the update_at logic in the application
-- timestamp is also not y2038-compliant; datetime is up to y9999

DROP TABLE IF EXISTS users;

CREATE TABLE users (
	id int not null auto_increment primary key,
	name varchar(255),
	openid_identity varchar(255) not null unique,
	email varchar(255),
	is_admin tinyint not null default 0,
	is_system tinyint not null default 0,
	created_at timestamp not null default current_timestamp,
	updated_at datetime,
	last_login datetime,
	
	is_premium tinyint not null default 0,
	premium_expires datetime,
	is_reminder_sent tinyint not null default 0,
	
	INDEX(openid_identity), INDEX(is_premium), INDEX(is_admin), INDEX(is_system)
);

INSERT INTO users SET id=100,name='System',openid_identity='http://openclerk.org/',email='support@openclerk.org',is_admin=0,is_system=1;

DROP TABLE IF EXISTS valid_user_keys;

CREATE TABLE valid_user_keys (
	id int not null auto_increment primary key,
	user_id int not null,
	user_key varchar(64) not null unique,
	created_at timestamp not null default current_timestamp,
	INDEX (user_id),
	INDEX (user_key)
);

-- recent uncaught exceptions
DROP TABLE IF EXISTS uncaught_exceptions;

CREATE TABLE uncaught_exceptions (
	id int not null auto_increment primary key,
	message varchar(255),
	previous_message varchar(255),
	filename varchar(255),
	line_number int,
	raw blob not null,
	class_name varchar(64),
	created_at timestamp not null default current_timestamp,
	
	job_id int,	-- may have been generated as part of a job
	
	INDEX(job_id), INDEX(class_name)
);

-- OpenClerk information starts here

-- all of the different account types that users can have --

DROP TABLE IF EXISTS accounts_btce;

CREATE TABLE accounts_btce (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,

	title varchar(255),
	api_key varchar(255) not null,
	api_secret varchar(255) not null,

	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_poolx;

CREATE TABLE accounts_poolx (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_mtgox;

CREATE TABLE accounts_mtgox (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	api_secret varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_vircurex;

CREATE TABLE accounts_vircurex (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_username varchar(255) not null,
	api_secret varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_litecoinglobal;

CREATE TABLE accounts_litecoinglobal (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- litecoinglobal has a range of securities; as we find new securities
-- (through users) we add these as normal queued jobs as well (using the securities user)
-- and can then use these to calculate balances. this however means that
-- we don't keep track of security counts/etc per user over time, just overall balance.

DROP TABLE IF EXISTS securities_litecoinglobal;

CREATE TABLE securities_litecoinglobal (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	name varchar(64) not null,
	
	INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_btct;

CREATE TABLE accounts_btct (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- same with btct

DROP TABLE IF EXISTS securities_btct;

CREATE TABLE securities_btct (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	name varchar(64) not null,
	
	INDEX(last_queue)
);

-- generic API requests

DROP TABLE IF EXISTS accounts_generic;

CREATE TABLE accounts_generic (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	currency varchar(3),
	api_url varchar(255) not null,
	
	INDEX(user_id), INDEX(currency), INDEX(last_queue)
);

-- all accounts (but not addresses) are summarised into balances

DROP TABLE IF EXISTS balances;

CREATE TABLE balances (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	
	exchange varchar(32) not null, -- e.g. btce, btc, ltc, poolx, bitnz, generic, ...
	account_id int not null,
	-- we dont need to worry too much about precision
	balance decimal(16,8) not null,
	currency varchar(3) not null,
	is_recent tinyint not null default 0,
	
	INDEX(user_id), INDEX(exchange), INDEX(currency), INDEX(is_recent), INDEX(account_id)
);

-- all of the different crypto addresses that users can have, and their balances --

DROP TABLE IF EXISTS addresses;

CREATE TABLE addresses (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,

	currency varchar(3) not null,
	address varchar(36) not null,
	
	INDEX(currency), INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS address_balances;

CREATE TABLE address_balances (
	id int not null auto_increment primary key,
	user_id int not null,
	address_id int not null,
	created_at timestamp not null default current_timestamp,
	
	balance decimal(16,8) not null,
	is_recent tinyint not null default 0,
	
	INDEX(user_id), INDEX(address_id), INDEX(is_recent)
);

-- Litecoin explorer does not let you specify confirmations parameter,
-- so we need to keep track of current block number (stored locally
-- so we don't have to request Explorer twice)
DROP TABLE IF EXISTS litecoin_blocks;

CREATE TABLE litecoin_blocks (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	blockcount int not null,
	
	is_recent tinyint not null default 0,
	
	INDEX(is_recent)
);

-- users can also specify offsets for non-API values --

DROP TABLE IF EXISTS offsets;

CREATE TABLE offsets (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	
	currency varchar(3) not null,
	balance decimal(16,8) not null,
	
	is_recent tinyint not null default 0,
	
	-- TODO titles/descriptions?
	
	INDEX(user_id), INDEX(currency), INDEX(is_recent)
);

-- all of the different exchanges that provide ticker data --

DROP TABLE IF EXISTS exchanges;

CREATE TABLE exchanges (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	name varchar(32) not null unique,
	last_queue datetime,
	-- this just stores last updated, not what currencies to download etc (defined in PHP)
	-- and also defines unique names for exchanges

	INDEX(last_queue), INDEX(name)
);

INSERT INTO exchanges SET name='btce';
INSERT INTO exchanges SET name='bitnz';
INSERT INTO exchanges SET name='mtgox';
INSERT INTO exchanges SET name='vircurex';

DROP TABLE IF EXISTS ticker;

CREATE TABLE ticker (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	exchange varchar(32) not null, -- no point to have exchange_id, that's just extra queries
	
	currency1 varchar(3),
	currency2 varchar(3),

	-- we don't need to worry too much about precision
	last_trade decimal(16,8),
	buy decimal(16,8),
	sell decimal(16,8),
	volume decimal(16,8),

	is_recent tinyint not null default 0,
	
	-- derived indexes; rather than creating some query 'GROUP BY date_format(created_at, '%d-%m-%Y')',
	-- we can use a simple flag to mark daily data.
	-- only a single row with this index will ever be present for a single day.
	-- this same logic could be further composed into hourly/etc data.
	-- this field is updated when jobs are executed.
	is_daily_data tinyint not null default 0,

	INDEX(exchange), INDEX(currency1), INDEX(currency2), INDEX(is_recent), INDEX(is_daily_data)
);

-- and we want to provide summary data for users --

DROP TABLE IF EXISTS summaries;

CREATE TABLE summaries (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	summary_type varchar(32) not null,
	
	INDEX(summary_type), INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS summary_instances;

CREATE TABLE summary_instances (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	summary_type varchar(32) not null,
	
	is_recent tinyint not null default 0,
	
	-- we dont need to worry too much about precision
	balance decimal(16,8),
	
	-- derived indexes; rather than creating some query 'GROUP BY date_format(created_at, '%d-%m-%Y')',
	-- we can use a simple flag to mark daily data.
	-- only a single row with this index will ever be present for a single day.
	-- this same logic could be further composed into hourly/etc data.
	-- this field is updated when jobs are executed.
	is_daily_data tinyint not null default 0,
	
	INDEX(summary_type), INDEX(user_id), INDEX(is_recent), INDEX(is_daily_data)
);

-- to request data, we insert in jobs

DROP TABLE IF EXISTS jobs;

CREATE TABLE jobs (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	priority tinyint not null default 10, -- lower value = higher priority
	
	job_type varchar(32) not null,
	user_id int not null,	-- requesting user ID, may be system ID (100)
	arg_id int,	-- argument for the job, a foreign key ID; may be null
	
	is_executed tinyint not null default 0,
	is_error tinyint not null default 0,	-- was an exception thrown while processing?
	
	executed_at datetime,
	
	INDEX(job_type), INDEX(priority), INDEX(user_id), INDEX(is_executed), INDEX(is_error)
);

-- users define graphs for their home page, split across pages

DROP TABLE IF EXISTS graph_pages;

CREATE TABLE graph_pages (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	updated_at datetime,
	user_id int not null,	-- requesting user ID, may be system ID (100)
	
	title varchar(64) not null,
	page_order tinyint default 0,		-- probably a good maximum number of pages, 256	

	is_removed tinyint not null default 0,		-- not displayed; not deleted in case we want to undo
	
	INDEX(user_id), INDEX(is_removed)
);

DROP TABLE IF EXISTS graphs;

CREATE TABLE graphs (
	id int not null auto_increment primary key,
	page_id int not null,
	created_at timestamp not null default current_timestamp,
	
	graph_type varchar(32) not null,
	arg0 int, 		-- some graphs have integer arguments
	width tinyint default 2,	-- e.g. 1 = half size, 2 = normal size, 4 = extra wide
	height tinyint default 2,		
	page_order tinyint default 0,		-- probably a good maximum number of graphs, 256	
	
	is_removed tinyint not null default 0,		-- not displayed; not deleted in case we want to undo
	
	INDEX(page_id), INDEX(is_removed)

);

-- premium account requests

DROP TABLE IF EXISTS outstanding_premiums;

CREATE TABLE outstanding_premiums (
	id int not null auto_increment primary key,
	user_id int not null,

	created_at timestamp not null default current_timestamp,
	paid_at datetime not null default 0,
	is_paid tinyint not null default 0,
	is_unpaid tinyint not null default 0,		-- this has never been paid after a very long time, so it's abandoned
	last_queue datetime,
	
	premium_address_id int not null, -- source address
	balance decimal(16,8),
	
	-- premium information
	months tinyint not null,
	years tinyint not null,

	-- we might as well reuse the existing infrastructure we have for checking address balances
	address_id int, -- target address in addresses 
	
	INDEX(user_id), INDEX(address_id), INDEX(premium_address_id), INDEX(is_paid)
);

-- when making a new purchase, we add the address as an address to the System user,
-- which is then checked as normal. we can then check on the balance of that address
-- to find out when it has been paid.
DROP TABLE IF EXISTS premium_addresses;

CREATE TABLE premium_addresses (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	is_used tinyint not null default 0,	-- i.e. is used in an outstanding_premiums
	used_at datetime,
	
	address varchar(36) not null,
	currency varchar(3) not null,
	
	INDEX(is_used), INDEX(currency)
);

-- keep track of external APIs; rather than pulling this from the database in real time, we
-- have a job that will regularly update this data. this data will be constant
-- outside of the update period.
DROP TABLE IF EXISTS external_status;

CREATE TABLE external_status (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	job_type varchar(32) not null,
	job_count int not null,
	job_errors int not null,
	job_first datetime not null,
	job_last datetime not null,
	sample_size int not null
);

-- updates since 0.1

-- eventually summary and ticker data is converted into a "graph" format
DROP TABLE IF EXISTS graph_data_ticker;

CREATE TABLE graph_data_ticker (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	exchange varchar(32) not null, -- no point to have exchange_id, that's just extra queries
	
	currency1 varchar(3),
	currency2 varchar(3),
	
	-- currently all stored graph data is daily
	data_date timestamp not null,	-- the time of this day should be truncated to 0:00 UTC, representing the next 24 hours
	samples int not null,	-- how many samples was this data obtained from?

	buy decimal(16,8),	-- last buy of the day: preserves current behaviour
	sell decimal(16,8),	-- last sell of the day: preserves current behaviour
	volume decimal(16,8),	-- maximum volume of the day
	
	-- for candlestick plots (eventually)
	last_trade_min decimal(16,8),
	last_trade_opening decimal(16,8),
	last_trade_closing decimal(16,8),
	last_trade_max decimal(16,8),

	INDEX(exchange), INDEX(currency1), INDEX(currency2), INDEX(data_date), UNIQUE(exchange, currency1, currency2, data_date)
);

DROP TABLE IF EXISTS graph_data_summary;

CREATE TABLE graph_data_summary (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	user_id int not null,
	summary_type varchar(32) not null,
	
	-- currently all stored graph data is daily
	data_date timestamp not null,	-- the time of this day should be truncated to 0:00 UTC, representing the next 24 hours
	samples int not null,	-- how many samples was this data obtained from?
	
	-- for candlestick plots (eventually)
	balance_min decimal(16,8),
	balance_opening decimal(16,8),
	balance_closing decimal(16,8),	-- also preserves current behaviour
	balance_max decimal(16,8),

	INDEX(user_id), INDEX(summary_type), INDEX(data_date), UNIQUE(user_id, summary_type, data_date)
);

-- in the future we could add graph_data_balances as necessary

-- all2btc is actually crypto2btc, since it doesn't consider fiat
UPDATE summary_instances SET summary_type='crypto2btc' WHERE summary_type='all2btc';

UPDATE graphs SET graph_type='total_converted_table' WHERE graph_type='fiat_converted_table';

----------------------------------------------------------------------------
-- upgrade statements from 0.1 to 0.2
----------------------------------------------------------------------------
DROP TABLE IF EXISTS feathercoin_blocks;

CREATE TABLE feathercoin_blocks (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	blockcount int not null,
	
	is_recent tinyint not null default 0,
	
	INDEX(is_recent)
);

DROP TABLE IF EXISTS accounts_cryptostocks;

CREATE TABLE accounts_cryptostocks (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_email varchar(255) not null,
	api_key_coin varchar(255) not null,
	api_key_share varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- as per litecoinglobal/btct

DROP TABLE IF EXISTS securities_cryptostocks;

CREATE TABLE securities_cryptostocks (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	name varchar(64) not null,
	currency varchar(3),	-- only null until we've retrieved the security definition
	
	INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_slush;

CREATE TABLE accounts_slush (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_token varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_wemineltc;

CREATE TABLE accounts_wemineltc (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_givemeltc;

CREATE TABLE accounts_givemeltc (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

----------------------------------------------------------------------------
-- upgrade statements from 0.2 to 0.3
----------------------------------------------------------------------------
DROP TABLE IF EXISTS accounts_bips;

CREATE TABLE accounts_bips (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

ALTER TABLE graphs ADD days int;

DROP TABLE IF EXISTS accounts_btcguild;

CREATE TABLE accounts_btcguild (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_50btc;

CREATE TABLE accounts_50btc (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

ALTER TABLE jobs ADD execution_count tinyint not null default 0;

-- update old jobs
UPDATE jobs SET execution_count=1 WHERE is_executed=1;

-- prevent POST DDoS of login page
CREATE TABLE heavy_requests (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	user_ip varchar(64) not null unique,	-- long string for IPv6, lets us block heavy requests based on IP
	last_request timestamp not null,
	
	INDEX(user_ip)
);

----------------------------------------------------------------------------
-- upgrade statements from 0.3 to 0.4
----------------------------------------------------------------------------
CREATE TABLE graph_technicals (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,

	graph_id int not null,
	technical_type varchar(32) not null,		-- e.g. 'bollinger'
	technical_period tinyint,			-- e.g. 10
	
	INDEX(graph_id)	
);

-- necessary for tax purposes
ALTER TABLE users ADD country varchar(4) not null;
ALTER TABLE users ADD user_ip varchar(64) not null;	-- long string for IPv6

-- addresses can have titles
ALTER TABLE addresses ADD title varchar(255);

-- if a job takes more than <refresh> secs, we shouldn't be executing it simultaneously
ALTER TABLE jobs ADD is_executing tinyint not null default 0;
ALTER TABLE jobs ADD INDEX(is_executing);

----------------------------------------------------------------------------
-- upgrade statements from 0.4 to 0.5
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------
DROP TABLE IF EXISTS accounts_hypernova;

CREATE TABLE accounts_hypernova (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_ltcmineru;

CREATE TABLE accounts_ltcmineru (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- we now also summarise balance data
DROP TABLE IF EXISTS graph_data_balances;

CREATE TABLE graph_data_balances (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	user_id int not null,
	exchange varchar(32) not null,
	account_id int not null,
	currency varchar(3) not null,
	
	-- currently all stored graph data is daily
	data_date timestamp not null,	-- the time of this day should be truncated to 0:00 UTC, representing the next 24 hours
	samples int not null,	-- how many samples was this data obtained from?
	
	-- for candlestick plots (eventually)
	balance_min decimal(16,8),
	balance_opening decimal(16,8),
	balance_closing decimal(16,8),	-- also preserves current behaviour
	balance_max decimal(16,8),

	INDEX(user_id), INDEX(exchange), INDEX(data_date), UNIQUE(user_id, exchange, account_id, currency, data_date)
);

ALTER TABLE balances ADD is_daily_data tinyint not null default 0;
ALTER TABLE balances ADD INDEX(is_daily_data);

-- update existing balances with is_daily_data flag
UPDATE balances SET is_daily_data=0;
CREATE TABLE temp (id int not null, user_id int not null, exchange varchar(255) not null, currency varchar(6) not null, account_id int);
INSERT INTO temp (id, user_id, exchange, currency, account_id) SELECT MAX(id) AS id, user_id, exchange, currency, account_id FROM balances GROUP BY date_format(created_at, '%d-%m-%Y'), user_id, exchange, currency, account_id;
-- NOTE this can take a very long time to execute if there are many rows; this is because the entire dependent subquery is retrieved on every row
-- a much faster approach is to split up the execution into many smaller subtables:
--    CREATE TABLE temp2 (id int not null); INSERT INTO temp2 (SELECT id FROM temp WHERE id >= 0 AND id < (0 + 1000));
--    UPDATE balances SET is_daily_data=1 WHERE id >= 0 AND id < (0 + 1000) AND (id) IN (SELECT id FROM temp2);
--    DROP TABLE temp2;
-- (etc)
UPDATE balances SET is_daily_data=1 WHERE (id, user_id, exchange, currency, account_id) IN (SELECT * FROM temp);
DROP TABLE temp;

-- so we can debug failing balances etc
ALTER TABLE balances ADD job_id INT;
ALTER TABLE summary_instances ADD job_id INT;
ALTER TABLE ticker ADD job_id INT;

-- so that we calculate summaries - before conversions - as a whole, to prevent cases where all2btc relies on totalltc (which may have changed)
ALTER TABLE users ADD last_queue datetime;

-- these don't make any sense and will always be zero
DELETE FROM summary_instances WHERE summary_type='blockchainusd';
DELETE FROM summary_instances WHERE summary_type='blockchainnzd';
DELETE FROM summary_instances WHERE summary_type='blockchaineur';
DELETE FROM graph_data_summary WHERE summary_type='blockchainusd';
DELETE FROM graph_data_summary WHERE summary_type='blockchainnzd';
DELETE FROM graph_data_summary WHERE summary_type='blockchaineur';

-- rather than storing mining rates as balances, we store them in a separate table -
-- this makes it cleaner to do mining rate summaries per currency, without polluting the
-- balances table
DROP TABLE IF EXISTS hashrates;

CREATE TABLE hashrates (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	
	exchange varchar(32) not null, -- e.g. btce, btc, ltc, poolx, bitnz, generic, ...
	account_id int not null,
	-- we dont need to worry too much about precision
	mhash float not null,		-- always in mhash
	currency varchar(3) not null,		-- e.g. slush will insert a btc and a nmc hashrate
	is_recent tinyint not null default 0,
	is_daily_data tinyint not null default 0,
	job_id int,
	
	INDEX(user_id), INDEX(exchange), INDEX(currency), INDEX(is_recent), INDEX(account_id), INDEX(is_daily_data)
);

-- precision isn't particularly important for stdev, since it's statistical anyway
ALTER TABLE graph_data_balances ADD balance_stdev float;
ALTER TABLE graph_data_summary ADD balance_stdev float;
ALTER TABLE graph_data_ticker ADD last_trade_stdev float;

----------------------------------------------------------------------------
-- upgrade statements from 0.5 to 0.6
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------
ALTER TABLE users ADD referer VARCHAR(255);

-- system-specific job queues that still need to be queued as appropriate
CREATE TABLE securities_update (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	exchange varchar(64) not null,
	
	INDEX(last_queue)
);

INSERT INTO securities_update SET exchange='btct';
INSERT INTO securities_update SET exchange='litecoinglobal';

-- so we have a history of external status
ALTER TABLE external_status ADD is_recent tinyint not null default 0;
ALTER TABLE external_status ADD INDEX(is_recent);

-- an integer index -> external API status table
CREATE TABLE external_status_types (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,

	job_type varchar(32) not null unique
);

-- technical_period can be > 128
ALTER TABLE graph_technicals MODIFY technical_period smallint;

DROP TABLE IF EXISTS accounts_miningforeman;

CREATE TABLE accounts_miningforeman (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_havelock;

CREATE TABLE accounts_havelock (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS securities_havelock;

CREATE TABLE securities_havelock (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	name varchar(64) not null,
	
	INDEX(last_queue)
);

INSERT INTO securities_update SET exchange='havelock';

-- we will remind the user regularly, up to a certain number of reminders, that this payment is overdue
ALTER TABLE outstanding_premiums ADD last_reminder datetime;
ALTER TABLE outstanding_premiums ADD cancelled_at datetime;

-- for automatically disabling users that have not logged in recently
ALTER TABLE users ADD is_disabled tinyint not null default 0;
ALTER TABLE users ADD INDEX(is_disabled);

ALTER TABLE users ADD disabled_at datetime;
ALTER TABLE users ADD disable_warned_at datetime;
ALTER TABLE users ADD is_disable_warned tinyint not null default 0;

-- because autologin never updated users last_login correctly, we'll give all old users the benefit of the doubt and say they've
-- logged in at the time of upgrade, so that old accounts are not all suddenly disabled
UPDATE users SET last_login=NOW();

-- periodically, create site statistics
DROP TABLE IF EXISTS site_statistics;
CREATE TABLE site_statistics (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	is_recent tinyint not null default 0,
	
	total_users int not null,
	disabled_users int not null,
	premium_users int not null,
	
	free_delay_minutes int not null,
	premium_delay_minutes int not null,
	outstanding_jobs int not null,
	external_status_job_count int not null,	-- equal to 'sample_size'
	external_status_job_errors int not null,
	
	mysql_uptime int not null,		-- 'Uptime'
	mysql_threads int not null,		-- 'Threads_running'
	mysql_questions int not null,		-- 'Questions'
	mysql_slow_queries int not null, 	-- 'Slow_queries'
	mysql_opens int not null,		-- 'Opened_tables'
	mysql_flush_tables int not null, 	-- 'Flush_commands'
	mysql_open_tables int not null, 	-- 'Open_tables'
	-- mysql_qps_average int not null, // can get qps = questions/uptime
	
	INDEX(is_recent)
);

----------------------------------------------------------------------------
-- upgrade statements from 0.6 to 0.7
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------

-- rather than using 'current balance' for payments, we should be using 'total received'
ALTER TABLE addresses ADD is_received tinyint not null default 0;

-- since we now handle partial payments, need to record how much was paid for each premium
ALTER TABLE outstanding_premiums ADD paid_balance decimal(16,8) default 0;
UPDATE outstanding_premiums SET paid_balance=balance WHERE is_paid=1;	-- update old data

DROP TABLE IF EXISTS ppcoin_blocks;

CREATE TABLE ppcoin_blocks (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	blockcount int not null,
	
	is_recent tinyint not null default 0,
	
	INDEX(is_recent)
);

DROP TABLE IF EXISTS accounts_miningforeman_ftc;

CREATE TABLE accounts_miningforeman_ftc (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- we now keep track of which securities each user has
-- but we don't yet keep track of is_daily_data etc, necessary for graphing quantities etc over time
DROP TABLE IF EXISTS securities;

CREATE TABLE securities (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	
	exchange varchar(32) not null,		-- e.g. btct, litecoinglobal
	security_id int not null,		-- e.g. id from securites_btct
	
	quantity int not null,	-- assumes integer value
	is_recent tinyint not null default 0,
	
	INDEX(user_id), INDEX(exchange, security_id), INDEX(is_recent)
);

-- for 'heading' graph type
ALTER TABLE graphs ADD string0 varchar(128);

----------------------------------------------------------------------------
-- upgrade statements from 0.7 to 0.8
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------

ALTER TABLE site_statistics ADD system_load_1min int;
ALTER TABLE site_statistics ADD system_load_5min int;
ALTER TABLE site_statistics ADD system_load_15min int;

CREATE TABLE accounts_bitminter (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

CREATE TABLE accounts_mine_litecoin (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- rename all old securities balances to _wallet, since now we can track
-- wallets and balances separately
UPDATE balances SET exchange='btct_wallet' WHERE exchange='btct';
UPDATE balances SET exchange='litecoinglobal_wallet' WHERE exchange='litecoinglobal';
UPDATE balances SET exchange='cryptostocks_wallet' WHERE exchange='cryptostocks';
UPDATE balances SET exchange='havelock_wallet' WHERE exchange='havelock';

UPDATE graph_data_balances SET exchange='btct_wallet' WHERE exchange='btct';
UPDATE graph_data_balances SET exchange='litecoinglobal_wallet' WHERE exchange='litecoinglobal';
UPDATE graph_data_balances SET exchange='cryptostocks_wallet' WHERE exchange='cryptostocks';
UPDATE graph_data_balances SET exchange='havelock_wallet' WHERE exchange='havelock';

CREATE TABLE accounts_liteguardian (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- fiat currency data from themoneyconverter.com
INSERT INTO exchanges SET name='themoneyconverter';

-- CAD/BTC
INSERT INTO exchanges SET name='virtex';

-- USD/BTC
INSERT INTO exchanges SET name='bitstamp';

----------------------------------------------------------------------------
-- upgrade statements from 0.8 to 0.9
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------

-- LTC, FTC, BTC all use the same API key
DROP TABLE IF EXISTS accounts_givemecoins;

CREATE TABLE accounts_givemecoins (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- set all old givemeltc balances to 0, so that they don't interfere with our summary calculations
UPDATE balances SET balance=0 WHERE exchange='givemeltc' AND is_recent=1;
UPDATE hashrates SET mhash=0 WHERE exchange='givemeltc' AND is_recent=1;

-- we can actually copy these over
INSERT INTO accounts_givemecoins (user_id, created_at, last_queue, title, api_key) (SELECT user_id, created_at, last_queue, title, api_key FROM accounts_givemeltc);

-- disable old mine_litecoin balances
UPDATE balances SET balance=0 WHERE exchange='mine_litecoin' AND is_recent=1;
UPDATE hashrates SET mhash=0 WHERE exchange='mine_litecoin' AND is_recent=1;

-- managed graph functionality
ALTER TABLE users ADD graph_managed_type varchar(16) not null default 'none';	-- 'none', 'auto', 'preferences'
ALTER TABLE users ADD INDEX(graph_managed_type);
ALTER TABLE users ADD preferred_crypto varchar(3) not null default 'btc';	-- preferred cryptocurrency
ALTER TABLE users ADD preferred_fiat varchar(3) not null default 'usd';	-- preferred fiat currency

ALTER TABLE users ADD needs_managed_update tinyint not null default 0;	-- graph_managed_type = auto or managed, and we need to update our graphs on next profile load

-- graph management preferences
CREATE TABLE managed_graphs (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,

	preference varchar(32) not null,

	INDEX(user_id)
);

-- was this graph added automatically?
ALTER TABLE graphs ADD is_managed tinyint not null default 0;
ALTER TABLE graph_pages ADD is_managed tinyint not null default 0;

ALTER TABLE graphs ADD INDEX(is_managed);
ALTER TABLE graph_pages ADD INDEX(is_managed);

-- page_order can be < 9000 due to managed graphs
-- (alternatively we could write complicated logic to reorder new & existing graphs based on their intended order)
ALTER TABLE graphs MODIFY page_order smallint default 0;

-- rename summary_nzd to summary_nzd_bitnz to fix wizard bug
UPDATE summaries SET summary_type='summary_nzd_bitnz' WHERE summary_type='summary_nzd';
UPDATE summary_instances SET summary_type='all2nzd_bitnz' WHERE summary_type='all2nzd';
UPDATE graph_data_summary SET summary_type='all2nzd_bitnz' WHERE summary_type='all2nzd';
UPDATE graphs SET graph_type='all2nzd_bitnz_daily' WHERE graph_type='all2nzd_daily';

ALTER TABLE users ADD last_managed_update datetime;

-- new signup form: 'subscribe to site announcements' field
ALTER TABLE users ADD subscribe_announcements tinyint not null default 0;

-- new subscriptions/unsubscriptions will be placed in here, so that they
-- can be processed manually (since google groups doesn't have an API)
CREATE TABLE pending_subscriptions (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	
	is_subscribe tinyint not null default 0,

	INDEX(user_id)
);

-- fields to send users a message when their first reports have been completed
-- (and, eventually into automated emails)
ALTER TABLE users ADD last_report_queue datetime;
ALTER TABLE users ADD is_first_report_sent tinyint not null default 0;
ALTER TABLE users ADD INDEX(is_first_report_sent);

-- all old users will not receive a first report
UPDATE users SET is_first_report_sent=1 WHERE DATE_ADD(created_at, INTERVAL 1 DAY) < NOW();

-- BitFunder publishes all asset owners to a public .json file, so
-- we only have to request this file once per hour (as per premium users)
INSERT INTO securities_update SET exchange='bitfunder';

DROP TABLE IF EXISTS securities_bitfunder;

CREATE TABLE securities_bitfunder (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	name varchar(64) not null,
	
	INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_bitfunder;

CREATE TABLE accounts_bitfunder (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	btc_address varchar(64) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- more site statistics
ALTER TABLE site_statistics ADD users_graphs_managed_none int;
ALTER TABLE site_statistics ADD users_graphs_managed_managed int;
ALTER TABLE site_statistics ADD users_graphs_managed_auto int;
ALTER TABLE site_statistics ADD users_graphs_need_update int;
ALTER TABLE site_statistics ADD users_subscribe_announcements int;
ALTER TABLE site_statistics ADD pending_subscriptions int;
ALTER TABLE site_statistics ADD pending_unsubscriptions int;

ALTER TABLE users ADD logins_after_disable_warned tinyint not null default 0;	-- not just a switch, but a count
ALTER TABLE site_statistics ADD user_logins_after_warned int;	-- total count
ALTER TABLE site_statistics ADD users_login_after_warned int;	-- total users
ALTER TABLE users ADD logins_after_disabled tinyint not null default 0;	-- not just a switch, but a count
ALTER TABLE site_statistics ADD user_logins_after_disabled int;	-- total count
ALTER TABLE site_statistics ADD users_login_after_disabled int;	-- total users

ALTER TABLE site_statistics MODIFY system_load_1min float;
ALTER TABLE site_statistics MODIFY system_load_5min float;
ALTER TABLE site_statistics MODIFY system_load_15min float;

----------------------------------------------------------------------------
-- upgrade statements from 0.9 to 0.10
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------
DROP TABLE IF EXISTS novacoin_blocks;

CREATE TABLE novacoin_blocks (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	
	blockcount int not null,
	
	is_recent tinyint not null default 0,
	
	INDEX(is_recent)
);

-- drop old tables
DROP TABLE IF EXISTS accounts_givemeltc;
DROP TABLE IF EXISTS accounts_mine_litecoin;

DROP TABLE IF EXISTS accounts_khore;

CREATE TABLE accounts_khore (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- for privately-held securities
CREATE TABLE accounts_individual_litecoinglobal (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	quantity int not null,
	security_id int not null,	-- to securities_litecoinglobal
	
	INDEX(user_id), INDEX(last_queue), INDEX(security_id)
);

CREATE TABLE accounts_individual_btct (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	quantity int not null,
	security_id int not null,	-- to securities_btct
	
	INDEX(user_id), INDEX(last_queue), INDEX(security_id)
);

CREATE TABLE accounts_individual_bitfunder (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	quantity int not null,
	security_id int not null,	-- to securities_bitfunder
	
	INDEX(user_id), INDEX(last_queue), INDEX(security_id)
);

CREATE TABLE accounts_individual_cryptostocks (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	quantity int not null,
	security_id int not null,	-- to securities_cryptostocks
	
	INDEX(user_id), INDEX(last_queue), INDEX(security_id)
);

CREATE TABLE accounts_individual_havelock (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	quantity int not null,
	security_id int not null,	-- to securities_havelock
	
	INDEX(user_id), INDEX(last_queue), INDEX(security_id)
);

----------------------------------------------------------------------------
-- upgrade statements from 0.10 to 0.11
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------
DROP TABLE IF EXISTS accounts_cexio;

CREATE TABLE accounts_cexio (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	api_username varchar(255) not null,
	api_secret varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

INSERT INTO exchanges SET name='cexio';

INSERT INTO exchanges SET name='crypto-trade';

DROP TABLE IF EXISTS accounts_cryptotrade;

CREATE TABLE accounts_cryptotrade (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	api_secret varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS securities_cryptotrade;

CREATE TABLE securities_cryptotrade (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	name varchar(64) not null,
	currency varchar(4) not null,
	
	INDEX(last_queue)
);

-- we insert these securities manually for now
INSERT INTO securities_cryptotrade SET name='CTB', currency='btc';
INSERT INTO securities_cryptotrade SET name='CTL', currency='ltc';
INSERT INTO securities_cryptotrade SET name='ESB', currency='btc';
INSERT INTO securities_cryptotrade SET name='ESL', currency='ltc';

CREATE TABLE accounts_individual_cryptotrade (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	quantity int not null,
	security_id int not null,	-- to securities_cryptotrade
	
	INDEX(user_id), INDEX(last_queue), INDEX(security_id)
);

-- performance improvements due to MySQL slow queries log
ALTER TABLE securities_bitfunder ADD index(name);
ALTER TABLE securities_btct ADD index(name);
ALTER TABLE securities_cryptostocks ADD index(name);
ALTER TABLE securities_cryptotrade ADD index(name);
ALTER TABLE securities_havelock ADD index(name);
ALTER TABLE securities_litecoinglobal ADD index(name);

-- since wizard_addresses always searches for the most recent job for a given address,
-- adding an is_recent/is_archived flag will allow us to reduce the search set significantly
ALTER TABLE jobs ADD is_recent tinyint not null default 0;
ALTER TABLE jobs ADD INDEX(is_recent);

-- mark all jobs in the last 24 hours as recent; batch_run will eventually sort everything out
-- (this is better than freezing the production database with a very complex but correct query)
UPDATE jobs SET is_recent=1 WHERE is_executed=1 AND is_recent=0 AND executed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- allow users to trigger tests
ALTER TABLE jobs ADD is_test_job tinyint not null default 0;
ALTER TABLE jobs ADD INDEX(is_test_job);

-- for jobs that timeout
ALTER TABLE jobs ADD execution_started timestamp null;
ALTER TABLE jobs ADD is_timeout tinyint not null default 0;
ALTER TABLE jobs ADD INDEX(is_timeout);

ALTER TABLE site_statistics ADD jobs_tests int not null default 0;
ALTER TABLE site_statistics ADD jobs_timeout int not null default 0;

----------------------------------------------------------------------------
-- upgrade statements from 0.11 to 0.12
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------

-- all date columns should be timestamp not datetime, so that all values are stored local to UTC
ALTER TABLE accounts_50btc MODIFY last_queue timestamp null;
ALTER TABLE accounts_bips MODIFY last_queue timestamp null;
ALTER TABLE accounts_bitfunder MODIFY last_queue timestamp null;
ALTER TABLE accounts_bitminter MODIFY last_queue timestamp null;
ALTER TABLE accounts_btce MODIFY last_queue timestamp null;
ALTER TABLE accounts_btcguild MODIFY last_queue timestamp null;
ALTER TABLE accounts_btct MODIFY last_queue timestamp null;
ALTER TABLE accounts_cexio MODIFY last_queue timestamp null;
ALTER TABLE accounts_cryptostocks MODIFY last_queue timestamp null;
ALTER TABLE accounts_cryptotrade MODIFY last_queue timestamp null;
ALTER TABLE accounts_generic MODIFY last_queue timestamp null;
ALTER TABLE accounts_givemecoins MODIFY last_queue timestamp null;
ALTER TABLE accounts_havelock MODIFY last_queue timestamp null;
ALTER TABLE accounts_hypernova MODIFY last_queue timestamp null;
ALTER TABLE accounts_individual_bitfunder MODIFY last_queue timestamp null;
ALTER TABLE accounts_individual_btct MODIFY last_queue timestamp null;
ALTER TABLE accounts_individual_cryptostocks MODIFY last_queue timestamp null;
ALTER TABLE accounts_individual_cryptotrade MODIFY last_queue timestamp null;
ALTER TABLE accounts_individual_havelock MODIFY last_queue timestamp null;
ALTER TABLE accounts_individual_litecoinglobal MODIFY last_queue timestamp null;
ALTER TABLE accounts_khore MODIFY last_queue timestamp null;
ALTER TABLE accounts_litecoinglobal MODIFY last_queue timestamp null;
ALTER TABLE accounts_liteguardian MODIFY last_queue timestamp null;
ALTER TABLE accounts_ltcmineru MODIFY last_queue timestamp null;
ALTER TABLE accounts_miningforeman MODIFY last_queue timestamp null;
ALTER TABLE accounts_miningforeman_ftc MODIFY last_queue timestamp null;
ALTER TABLE accounts_mtgox MODIFY last_queue timestamp null;
ALTER TABLE accounts_poolx MODIFY last_queue timestamp null;
ALTER TABLE accounts_slush MODIFY last_queue timestamp null;
ALTER TABLE accounts_vircurex MODIFY last_queue timestamp null;
ALTER TABLE accounts_wemineltc MODIFY last_queue timestamp null;
ALTER TABLE addresses MODIFY last_queue timestamp null;
ALTER TABLE exchanges MODIFY last_queue timestamp null;
ALTER TABLE external_status MODIFY job_first timestamp not null;
ALTER TABLE external_status MODIFY job_last timestamp not null;
ALTER TABLE graph_pages MODIFY updated_at timestamp null;
ALTER TABLE jobs MODIFY executed_at timestamp null;
ALTER TABLE outstanding_premiums MODIFY paid_at timestamp null;
ALTER TABLE outstanding_premiums MODIFY last_queue timestamp null;
ALTER TABLE outstanding_premiums MODIFY last_reminder timestamp null;
ALTER TABLE outstanding_premiums MODIFY cancelled_at timestamp null;
ALTER TABLE premium_addresses MODIFY used_at timestamp null;
ALTER TABLE securities_bitfunder MODIFY last_queue timestamp null;
ALTER TABLE securities_btct MODIFY last_queue timestamp null;
ALTER TABLE securities_cryptostocks MODIFY last_queue timestamp null;
ALTER TABLE securities_cryptotrade MODIFY last_queue timestamp null;
ALTER TABLE securities_havelock MODIFY last_queue timestamp null;
ALTER TABLE securities_litecoinglobal MODIFY last_queue timestamp null;
ALTER TABLE securities_update MODIFY last_queue timestamp null;
ALTER TABLE summaries MODIFY last_queue timestamp null;
ALTER TABLE users MODIFY updated_at timestamp null;
ALTER TABLE users MODIFY last_login timestamp null;
ALTER TABLE users MODIFY premium_expires timestamp null;
ALTER TABLE users MODIFY last_queue timestamp null;
ALTER TABLE users MODIFY disabled_at timestamp null;
ALTER TABLE users MODIFY disable_warned_at timestamp null;
ALTER TABLE users MODIFY last_managed_update timestamp null;
ALTER TABLE users MODIFY last_report_queue timestamp null;

-- add new site_space statistics
ALTER TABLE site_statistics ADD disk_free_space float;	-- precision isn't strictly necessary

-- remove orphaned _securities and _wallet balances
DELETE FROM balances WHERE exchange='litecoinglobal_securities' AND account_id NOT IN (SELECT id FROM accounts_litecoinglobal);
DELETE FROM balances WHERE exchange='litecoinglobal_wallet' AND account_id NOT IN (SELECT id FROM accounts_litecoinglobal);
DELETE FROM balances WHERE exchange='btct_securities' AND account_id NOT IN (SELECT id FROM accounts_btct);
DELETE FROM balances WHERE exchange='btct_wallet' AND account_id NOT IN (SELECT id FROM accounts_btct);
DELETE FROM balances WHERE exchange='crypto-trade_securities' AND account_id NOT IN (SELECT id FROM accounts_cryptotrade);
DELETE FROM balances WHERE exchange='crypto-trade_wallet' AND account_id NOT IN (SELECT id FROM accounts_cryptotrade);
DELETE FROM balances WHERE exchange='cryptostocks_securities' AND account_id NOT IN (SELECT id FROM accounts_cryptostocks);
DELETE FROM balances WHERE exchange='cryptostocks_wallet' AND account_id NOT IN (SELECT id FROM accounts_cryptostocks);
DELETE FROM balances WHERE exchange='havelock_securities' AND account_id NOT IN (SELECT id FROM accounts_havelock);
DELETE FROM balances WHERE exchange='havelock_wallet' AND account_id NOT IN (SELECT id FROM accounts_havelock);
DELETE FROM balances WHERE exchange='bitfunder_securities' AND account_id NOT IN (SELECT id FROM accounts_bitfunder);

-- database cleanup
DELETE FROM ticker WHERE currency1='nzd' AND currency2='btc' AND last_trade=0;
CREATE TABLE temp (id int);
INSERT INTO temp (SELECT user_id FROM summary_instances WHERE summary_type='all2nzd_bitnz' AND balance > 0 GROUP BY user_id);
DELETE FROM summary_instances WHERE summary_type='all2nzd_bitnz' AND balance=0 AND user_id IN (SELECT id FROM temp);
DROP TABLE temp;

-- track time between account creation and first report ready
ALTER TABLE users ADD first_report_sent timestamp null;
ALTER TABLE users ADD reminder_sent timestamp null;

DROP TABLE IF EXISTS accounts_bitstamp;

CREATE TABLE accounts_bitstamp (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_client_id int not null,
	api_key varchar(255) not null,
	api_secret varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_796;

CREATE TABLE accounts_796 (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_app_id int not null,
	api_key varchar(255) not null,
	api_secret varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- 796 doesn't have an API for listing securities or their names, so we enter them in manually

DROP TABLE IF EXISTS securities_796;

CREATE TABLE securities_796 (
	id int not null auto_increment primary key,
	created_at timestamp not null default current_timestamp,
	last_queue timestamp null,
	
	name varchar(64) not null,
	title varchar(64) not null,
	api_name varchar(64) not null,		-- because 'mri's API name is 'xchange' instead
	
	INDEX(last_queue)
);
INSERT INTO securities_796 SET name='mri', title='796Xchange-MRI', api_name='xchange';
INSERT INTO securities_796 SET name='asicminer', title='ASICMINER-796', api_name='asicminer';
INSERT INTO securities_796 SET name='bd', title='BTC-DICE-796', api_name='bd';

CREATE TABLE accounts_individual_796 (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue timestamp,
	
	title varchar(255),
	quantity int not null,
	security_id int not null,	-- to securities_796
	
	INDEX(user_id), INDEX(last_queue), INDEX(security_id)
);

ALTER TABLE users ADD securities_count int not null default 0;
ALTER TABLE users ADD securities_last_count_queue timestamp null;

DROP TABLE IF EXISTS accounts_kattare;

CREATE TABLE accounts_kattare (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue timestamp null,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

----------------------------------------------------------------------------
-- upgrade statements from 0.12 to 0.13
-- NOTE make sure you set jobs_enabled=false while upgrading the site and executing these queries!
----------------------------------------------------------------------------

-- accounts can now be disabled if they fail repeatedly
-- failing account tables need to have the following fields: is_disabled, failures, first_failure, title
-- and set 'failures' to true in account_data_grouped()
ALTER TABLE accounts_bitstamp ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_bitstamp ADD failures tinyint not null default 0;
ALTER TABLE accounts_bitstamp ADD first_failure timestamp null;
ALTER TABLE accounts_bitstamp ADD INDEX(is_disabled);

ALTER TABLE accounts_50btc ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_50btc ADD failures tinyint not null default 0;
ALTER TABLE accounts_50btc ADD first_failure timestamp null;
ALTER TABLE accounts_50btc ADD INDEX(is_disabled);

ALTER TABLE accounts_796 ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_796 ADD failures tinyint not null default 0;
ALTER TABLE accounts_796 ADD first_failure timestamp null;
ALTER TABLE accounts_796 ADD INDEX(is_disabled);

ALTER TABLE accounts_bips ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_bips ADD failures tinyint not null default 0;
ALTER TABLE accounts_bips ADD first_failure timestamp null;
ALTER TABLE accounts_bips ADD INDEX(is_disabled);

ALTER TABLE accounts_bitfunder ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_bitfunder ADD failures tinyint not null default 0;
ALTER TABLE accounts_bitfunder ADD first_failure timestamp null;
ALTER TABLE accounts_bitfunder ADD INDEX(is_disabled);

ALTER TABLE accounts_bitminter ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_bitminter ADD failures tinyint not null default 0;
ALTER TABLE accounts_bitminter ADD first_failure timestamp null;
ALTER TABLE accounts_bitminter ADD INDEX(is_disabled);

ALTER TABLE accounts_btce ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_btce ADD failures tinyint not null default 0;
ALTER TABLE accounts_btce ADD first_failure timestamp null;
ALTER TABLE accounts_btce ADD INDEX(is_disabled);

ALTER TABLE accounts_btcguild ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_btcguild ADD failures tinyint not null default 0;
ALTER TABLE accounts_btcguild ADD first_failure timestamp null;
ALTER TABLE accounts_btcguild ADD INDEX(is_disabled);

ALTER TABLE accounts_btct ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_btct ADD failures tinyint not null default 0;
ALTER TABLE accounts_btct ADD first_failure timestamp null;
ALTER TABLE accounts_btct ADD INDEX(is_disabled);

ALTER TABLE accounts_cexio ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_cexio ADD failures tinyint not null default 0;
ALTER TABLE accounts_cexio ADD first_failure timestamp null;
ALTER TABLE accounts_cexio ADD INDEX(is_disabled);

ALTER TABLE accounts_cryptostocks ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_cryptostocks ADD failures tinyint not null default 0;
ALTER TABLE accounts_cryptostocks ADD first_failure timestamp null;
ALTER TABLE accounts_cryptostocks ADD INDEX(is_disabled);

ALTER TABLE accounts_cryptotrade ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_cryptotrade ADD failures tinyint not null default 0;
ALTER TABLE accounts_cryptotrade ADD first_failure timestamp null;
ALTER TABLE accounts_cryptotrade ADD INDEX(is_disabled);

ALTER TABLE accounts_generic ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_generic ADD failures tinyint not null default 0;
ALTER TABLE accounts_generic ADD first_failure timestamp null;
ALTER TABLE accounts_generic ADD INDEX(is_disabled);

ALTER TABLE accounts_givemecoins ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_givemecoins ADD failures tinyint not null default 0;
ALTER TABLE accounts_givemecoins ADD first_failure timestamp null;
ALTER TABLE accounts_givemecoins ADD INDEX(is_disabled);

ALTER TABLE accounts_havelock ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_havelock ADD failures tinyint not null default 0;
ALTER TABLE accounts_havelock ADD first_failure timestamp null;
ALTER TABLE accounts_havelock ADD INDEX(is_disabled);

ALTER TABLE accounts_hypernova ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_hypernova ADD failures tinyint not null default 0;
ALTER TABLE accounts_hypernova ADD first_failure timestamp null;
ALTER TABLE accounts_hypernova ADD INDEX(is_disabled);

ALTER TABLE accounts_kattare ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_kattare ADD failures tinyint not null default 0;
ALTER TABLE accounts_kattare ADD first_failure timestamp null;
ALTER TABLE accounts_kattare ADD INDEX(is_disabled);

ALTER TABLE accounts_khore ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_khore ADD failures tinyint not null default 0;
ALTER TABLE accounts_khore ADD first_failure timestamp null;
ALTER TABLE accounts_khore ADD INDEX(is_disabled);

ALTER TABLE accounts_litecoinglobal ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_litecoinglobal ADD failures tinyint not null default 0;
ALTER TABLE accounts_litecoinglobal ADD first_failure timestamp null;
ALTER TABLE accounts_litecoinglobal ADD INDEX(is_disabled);

ALTER TABLE accounts_liteguardian ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_liteguardian ADD failures tinyint not null default 0;
ALTER TABLE accounts_liteguardian ADD first_failure timestamp null;
ALTER TABLE accounts_liteguardian ADD INDEX(is_disabled);

ALTER TABLE accounts_ltcmineru ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_ltcmineru ADD failures tinyint not null default 0;
ALTER TABLE accounts_ltcmineru ADD first_failure timestamp null;
ALTER TABLE accounts_ltcmineru ADD INDEX(is_disabled);

ALTER TABLE accounts_miningforeman ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_miningforeman ADD failures tinyint not null default 0;
ALTER TABLE accounts_miningforeman ADD first_failure timestamp null;
ALTER TABLE accounts_miningforeman ADD INDEX(is_disabled);

ALTER TABLE accounts_miningforeman_ftc ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_miningforeman_ftc ADD failures tinyint not null default 0;
ALTER TABLE accounts_miningforeman_ftc ADD first_failure timestamp null;
ALTER TABLE accounts_miningforeman_ftc ADD INDEX(is_disabled);

ALTER TABLE accounts_mtgox ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_mtgox ADD failures tinyint not null default 0;
ALTER TABLE accounts_mtgox ADD first_failure timestamp null;
ALTER TABLE accounts_mtgox ADD INDEX(is_disabled);

ALTER TABLE accounts_poolx ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_poolx ADD failures tinyint not null default 0;
ALTER TABLE accounts_poolx ADD first_failure timestamp null;
ALTER TABLE accounts_poolx ADD INDEX(is_disabled);

ALTER TABLE accounts_slush ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_slush ADD failures tinyint not null default 0;
ALTER TABLE accounts_slush ADD first_failure timestamp null;
ALTER TABLE accounts_slush ADD INDEX(is_disabled);

ALTER TABLE accounts_vircurex ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_vircurex ADD failures tinyint not null default 0;
ALTER TABLE accounts_vircurex ADD first_failure timestamp null;
ALTER TABLE accounts_vircurex ADD INDEX(is_disabled);

ALTER TABLE accounts_wemineltc ADD is_disabled tinyint not null default 0;
ALTER TABLE accounts_wemineltc ADD failures tinyint not null default 0;
ALTER TABLE accounts_wemineltc ADD first_failure timestamp null;
ALTER TABLE accounts_wemineltc ADD INDEX(is_disabled);

INSERT INTO exchanges SET name='btcchina';

INSERT INTO exchanges SET name='cryptsy';

DROP TABLE IF EXISTS accounts_litepooleu;

CREATE TABLE accounts_litepooleu (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	is_disabled tinyint not null default 0,
	failures tinyint not null default 0,
	first_failure timestamp null,
	
	INDEX(user_id), INDEX(last_queue), INDEX(is_disabled)
);

DROP TABLE IF EXISTS accounts_coinhuntr;

CREATE TABLE accounts_coinhuntr (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	is_disabled tinyint not null default 0,
	failures tinyint not null default 0,
	first_failure timestamp null,
	
	INDEX(user_id), INDEX(last_queue), INDEX(is_disabled)
);

DROP TABLE IF EXISTS accounts_eligius;

CREATE TABLE accounts_eligius (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	btc_address varchar(64) not null,
	
	is_disabled tinyint not null default 0,
	failures tinyint not null default 0,
	first_failure timestamp null,
	
	INDEX(user_id), INDEX(last_queue), INDEX(is_disabled)
);

-- for tracking unpaid balances on Eligius accounts
INSERT INTO securities_update SET exchange='eligius';

DROP TABLE IF EXISTS accounts_lite_coinpool;

CREATE TABLE accounts_lite_coinpool (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	is_disabled tinyint not null default 0,
	failures tinyint not null default 0,
	first_failure timestamp null,
	
	INDEX(user_id), INDEX(last_queue), INDEX(is_disabled)
);
