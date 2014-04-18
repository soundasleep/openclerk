Upgrading
=========

Based on the original Wiki content at http://code.google.com/p/openclerk/wiki/Upgrading.
Also see the pending issue for developing an upgrade script: [#115](http://redmine.jevon.org/issues/115)

Upgrading an Openclerk instance is currently a manual process but fairly
straightforward once you have done it a couple of times. The basic format is:

1. Update `config.php` and set jobs_enabled to false

1. Execute `svn update` to update to the latest version

1. Run `composer update` to update any composer dependencies

1. Execute `grunt build` to rebuild static assets

1. Execute the new database commands in `inc\database.sql` on the database server

1. Re-enable `jobs_enabled` to true

1. Check the [admin status page](http://localhost/clerk/admin_status) to make sure that no jobs are failing with errors, fix configuration as necessary
