DROP TABLE IF EXISTS users;

CREATE TABLE users (
	id int not null auto_increment primary key,
	name varchar(255),
	openid_identity varchar(255) not null unique,
	email varchar(255),
	is_admin tinyint not null default 0,
	created_at datetime not null default now(),
	updated_at datetime not null default now() on update now(),
	last_login datetime,
	
	is_premium tinyint not null default 0,
	premium_expires datetime,
	
	INDEX(openid_identity), INDEX(is_premium), INDEX(is_admin)
);

INSERT INTO users SET id=100,name='System',openid_identity='http://openclerk.org/',email='support@openclerk.org',is_admin=0;

DROP TABLE IF EXISTS valid_user_keys;

CREATE TABLE valid_user_keys (
	id int not null auto_increment primary key,
	user_id int not null,
	user_key varchar(64) not null unique,
	created_at datetime not null,
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
	created_at datetime not null,
	
	job_id int,	-- may have been generated as part of a job
	
	INDEX(job_id)
);

-- OpenClerk information starts here

-- all of the different account types that users can have --

DROP TABLE IF EXISTS accounts_btce;

CREATE TABLE accounts_btce (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
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
	created_at datetime not null default now(),
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS accounts_mtgox;

CREATE TABLE accounts_mtgox (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
	last_queue datetime,
	
	title varchar(255),
	api_key varchar(255) not null,
	api_secret varchar(255) not null,
	
	INDEX(user_id), INDEX(last_queue)
);

-- generic API requests

DROP TABLE IF EXISTS accounts_generic;

CREATE TABLE accounts_generic (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
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
	created_at datetime not null default now(),
	
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
	created_at datetime not null default now(),
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
	created_at datetime not null default now(),
	
	balance decimal(16,8) not null,
	is_recent tinyint not null default 0,
	
	INDEX(user_id), INDEX(address_id), INDEX(is_recent)
);

-- users can also specify offsets for non-API values --

DROP TABLE IF EXISTS offsets;

CREATE TABLE offsets (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
	
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
	created_at datetime not null default now(),
	
	name varchar(32) not null unique,
	last_queue datetime,
	-- this just stores last updated, not what currencies to download etc (defined in PHP)
	-- and also defines unique names for exchanges

	INDEX(last_queue), INDEX(name)
);

INSERT INTO exchanges SET name='btce';
INSERT INTO exchanges SET name='bitnz';
INSERT INTO exchanges SET name='mtgox';

DROP TABLE IF EXISTS ticker;

CREATE TABLE ticker (
	id int not null auto_increment primary key,
	created_at datetime not null default now(),
	
	exchange varchar(32) not null, -- no point to have exchange_id, that's just extra queries
	
	currency1 varchar(3),
	currency2 varchar(3),

	-- we don't need to worry too much about precision
	last_trade decimal(16,8),
	buy decimal(16,8),
	sell decimal(16,8),
	volume decimal(16,8),

	is_recent tinyint not null default 0,

	INDEX(exchange), INDEX(currency1), INDEX(currency2), INDEX(is_recent)
);

-- and we want to provide summary data for users --

DROP TABLE IF EXISTS summaries;

CREATE TABLE summaries (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
	last_queue datetime,
	
	summary_type varchar(32) not null,
	
	INDEX(summary_type), INDEX(user_id), INDEX(last_queue)
);

DROP TABLE IF EXISTS summary_instances;

CREATE TABLE summary_instances (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
	summary_type varchar(32) not null,
	
	is_recent tinyint not null default 0,
	
	-- we dont need to worry too much about precision
	balance decimal(16,8),
	
	INDEX(summary_type), INDEX(user_id), INDEX(is_recent)
);

-- to request data, we insert in jobs

DROP TABLE IF EXISTS jobs;

CREATE TABLE jobs (
	id int not null auto_increment primary key,
	created_at datetime not null default now(),
	
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
	created_at datetime not null default now(),
	updated_at datetime not null default now() on update now(),
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
	
	graph_type varchar(32) not null,
	arg0 int, 		-- some graphs have integer arguments
	width tinyint default 2,	-- e.g. 1 = half size, 2 = normal size, 4 = extra wide
	height tinyint default 2,		
	page_order tinyint default 0,		-- probably a good maximum number of graphs, 256	
	
	is_removed tinyint not null default 0,		-- not displayed; not deleted in case we want to undo
	
	INDEX(page_id), INDEX(is_removed)

);

