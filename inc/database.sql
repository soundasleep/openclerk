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

