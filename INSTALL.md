Installation
============

Welcome to Openclerk 0.12+. These installation instructions are still under
development - check out http://code.google.com/p/openclerk/issues/detail?id=3
for more information.

To install Openclerk:

1. Install MySQL: (requires MySQL 5.1+ or 5.5+ for Openclerk 0.12+)

        sudo apt-get install mysql-server php5-mysql

2. Install PHP/Apache: (requires PHP 5+)

        sudo apt-get install apache2 php5 php5-mysql php5-curl libapache2-mod-php5 php5-gd
        sudo a2enmod rewrite
        sudo service apache2 restart

3. Install all the build dependencies:

        # install Ruby
        apt-get install rubygems python-software-properties git

        # install NodeJS, npm
        add-apt-repository ppa:chris-lea/node.js
        apt-get update
        apt-get install nodejs        # also installs npm from latest

        # install Composer, globally
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer

        gem install sass
        npm install
        npm install -g grunt-cli
        composer install

1. Build through Grunt:

        grunt build

1. Or, if you are building through Jenkins, use these commands:

        npm install
        composer install
        mysql -u root --password=password < config/reset_clerk_database.sql
        mysql -u clerk --password=password clerk < inc/database.sql
        grunt test # JUnit output is in tests/report.xml

4. Configure Apache to serve both openclerk and `node_modules/` through the parent
   directory, as necessary: (serving `node_modules/` is not necessary if you are building
   the site through `grunt build`)

        Alias "/clerk/js/node_modules" "/var/www/my.openclerk.org/node_modules"
        <Directory "/var/www/my.openclerk.org/node_modules">
           Options Indexes FollowSymLinks
           DirectoryIndex index.html index.php default.html default.php
           AllowOverride All
           Allow from All
        </Directory>

        Alias "/clerk" "/var/www/my.openclerk.org/site"
        <Directory "/var/www/my.openclerk.org/site">
           Options Indexes FollowSymLinks
           DirectoryIndex index.html index.php default.html default.php
           AllowOverride All
           Allow from All
        </Directory>

4. Update `site/.htaccess` mod_rewrite rules if you are not running within a
   `/clerk` subfolder

5. Create a new database and new user:

        CREATE DATABASE openclerk;
        GRANT ALL ON openclerk.* to 'openclerk'@'localhost' IDENTIFIED BY 'password';

6. Initialise the database:

        mysql -u openclerk -p < inc/database.sql

7. Copy `inc/config.php.sample` to `config/config.php` and edit it with relevant configuration data.

8. Set up cron jobs to execute the `batch/batch_*.php` scripts as necessary. Set
   'automated_key' to a secure value, and use this as the first parameter
   when executing PHP scripts via CLI. For example:

        */1 * * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_run.php abc123
        */10 * * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_queue.php abc123
        0 */1 * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_external.php abc123
        30 */1 * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_statistics.php abc123

9. Sign up as normal. To make yourself an administrator, execute MySQL:

        UPDATE users SET is_admin=1 WHERE id=?
