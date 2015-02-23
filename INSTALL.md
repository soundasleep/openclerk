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
        php -f core/install.php
        grunt test                  # JUnit output is in tests/report.xml

4. Configure Apache to serve openclerk through the parent directory:

        Alias "/clerk" "/var/www/my.openclerk.org/site"
        <Directory "/var/www/my.openclerk.org/site">
           Options Indexes FollowSymLinks
           DirectoryIndex index.html index.php default.html default.php
           AllowOverride All
           Allow from All
           ErrorDocument /404.php
        </Directory>

5. Create a new MySQL database and new MySQL user:

        CREATE DATABASE openclerk;
        GRANT ALL ON openclerk.* to 'openclerk'@'localhost' IDENTIFIED BY 'password';

6. Initialise the database:

        php -f core/install.php

7. Edit `inc/config.php` as necessary, or create a `config/config.php` to overwrite
   these default configuration options.

8. Set up cron jobs to execute the `batch/batch_*.php` scripts as necessary. Set
   'automated_key' to a secure value, and use this as the first parameter
   when executing PHP scripts via CLI. For example:

        */1 * * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_run.php abc123
        */10 * * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_queue.php abc123
        0 */1 * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_external.php abc123
        30 */1 * * * cd /xxx/openclerk/batch && php -f /xxx/openclerk/batch/batch_statistics.php abc123

9. Sign up as normal. To make yourself an administrator, execute MySQL:

        UPDATE users SET is_admin=1 WHERE id=?

10. Visit the _Admin Migrations_ page to complete installing the database migrations (new as of 0.31).

### Or install with Chef

An experimental Chef cookbook that will install and configure Openclerk is available at https://github.com/soundasleep/openclerk-cookbook.
