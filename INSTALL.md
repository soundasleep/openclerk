Installation
============

Welcome to Openclerk 0.12+. These installation instructions are still under
development - check out http://code.google.com/p/openclerk/issues/detail?id=3
for more information.

To install Openclerk:

1. Install MySQL: (requires MySQL 5.1+ or 5.5+ for Openclerk 0.12+)

    sudo apt-get install mysql-server php5-mysql

1. Install PHP/Apache: (requires PHP 5+)

    sudo apt-get install apache2 php5 php5-mysql php5-curl
    sudo a2enmod rewrite
    sudo service apache2 restart

1. Install all the build dependencies:

    sudo apt-get install ...
    npm install
    grunt install
    composer install

1. Build through Grunt:

    grunt build

1. Update `site/.htaccess` mod_rewrite rules if you are not running within a
  `/clerk` subfolder

1. Create a new database and new user:

    CREATE DATABASE openclerk;
    GRANT ALL ON openclerk.* to 'openclerk'@'localhost' IDENTIFIED BY 'password';

1. Initialise the database:

    mysql -u openclerk -p < inc/database.sql

1. Copy inc/config.php.sample to inc/config.php and edit it with relevant
   configuration data.

1. Set up cron jobs to execute the `batch/batch_*.php` scripts as necessary. Set
   'automated_key' to a secure value, and use this as the first parameter
   when executing PHP scripts via CLI. For example:

    */1 * * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_run.php abc123
    */10 * * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_queue.php abc123
    0 */1 * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_external.php abc123
    30 */1 * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_statistics.php abc123

1. Sign up as normal. To make yourself an administrator, execute MySQL:

    UPDATE users SET is_admin=1 WHERE id=?
