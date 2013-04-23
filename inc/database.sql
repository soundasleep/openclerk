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
	INDEX(openid_identity)
);

INSERT INTO users SET id=100,name='System',openid_identity='http://openclerk.org/',email='support@openclerk.org',is_admin=0;
INSERT INTO users SET name='Admin',openid_identity='http://www.jevon.org/',email='jevon@jevon.org',is_admin=1;

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
	created_at datetime not null
);

-- OpenClerk information starts here

-- all of the different account types that users can have --

DROP TABLE IF EXISTS accounts_btce;

CREATE TABLE accounts_btce (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
	last_update datetime,

	api_key varchar(255) not null,
	api_secret varchar(255) not null,

	INDEX(user_id)
);

DROP TABLE IF EXISTS accounts_poolx;

CREATE TABLE accounts_poolx (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
	last_update datetime,
	
	api_key varchar(255) not null,
	
	INDEX(user_id)
);

-- generic API requests

DROP TABLE IF EXISTS accounts_generic;

CREATE TABLE accounts_generic (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
	last_update datetime,
	
	api_url varchar(255) not null,
	
	INDEX(user_id)
);

-- all accounts and addresses are summarised into balances

DROP TABLE IF EXISTS balances;

CREATE TABLE balances (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),
	last_update datetime,
	
	exchange varchar(32) not null, -- e.g. btce, btc, ltc, poolx, bitnz
	-- we dont need to worry too much about precision
	balance float not null,
	currency varchar(3) not null,
	
	INDEX(user_id), INDEX(exchange), INDEX(currency)
);

-- all of the different crypto addresses that users can have --

DROP TABLE IF EXISTS addresses;

CREATE TABLE addresses (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at datetime not null default now(),

	currency varchar(3) not null,
	address varchar(36) not null,
	
	INDEX(currency), INDEX(user_id)
);

-- all of the different exchanges that provide ticker data --

DROP TABLE IF EXISTS exchanges;

CREATE TABLE exchanges (
	id int not null auto_increment primary key,
	created_at datetime not null default now(),
	
	name varchar(32) not null unique,
	last_update datetime,
	-- this just stores last updated, not what currencies to download etc (defined in PHP)
	-- and also defines unique names for exchanges

	INDEX(last_update), INDEX(name)
);

INSERT INTO exchanges SET name='btce';
INSERT INTO exchanges SET name='bitnz';

DROP TABLE IF EXISTS ticker;

CREATE TABLE ticker (
	id int not null auto_increment primary key,
	created_at datetime not null default now(),
	
	exchange varchar(32) not null, -- no point to have exchange_id, that's just extra queries
	
	currency1 varchar(3),
	currency2 varchar(3),

	-- we don't need to worry too much about precision
	last_trade float,
	buy float,
	sell float,
	volume float,

	INDEX(exchange), INDEX(currency1), INDEX(currency2)
);

-- and we want to provide summary data for users --

DROP TABLE IF EXISTS user_summaries;

CREATE TABLE user_summaries (
	id int not null auto_increment primary key,
	created_at datetime not null default now(),
	summary_type varchar(32) not null,
	
	-- we dont need to worry too much about precision
	balance float,
	
	INDEX(summary_type)
);

-- to request data, we insert in jobs

DROP TABLE IF EXISTS jobs;

CREATE TABLE jobs (
	id int not null auto_increment primary key,
	created_at datetime not null default now(),
	
	priority tinyint not null default 0, -- lower value = higher priority
	
	job_type varchar(32) not null,
	user_id int not null,	-- requesting user ID, may be system ID (100)
	
	executed_at datetime,
	
	INDEX(job_type), INDEX(priority), INDEX(user_id)
);
