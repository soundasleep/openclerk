-- These database queries are only necessary if you are enabling the "unsafe" features
-- of openclerk, such as accounts with full trade APIs, etc.
--
-- You should only run these queries if you have enabled the unsafe flag:
-- see http://code.google.com/p/openclerk/wiki/Unsafe

DROP TABLE IF EXISTS accounts_cryptsy;

CREATE TABLE accounts_cryptsy (
	id int not null auto_increment primary key,
	user_id int not null,
	created_at timestamp not null default current_timestamp,
	last_queue timestamp,
	
	title varchar(255),
	api_public_key varchar(255) not null,
	api_private_key varchar(255) not null,
	
	is_disabled tinyint not null default 0,
	failures tinyint not null default 0,
	first_failure timestamp null,
	
	INDEX(user_id), INDEX(last_queue), INDEX(is_disabled)
);
