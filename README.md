openclerk
=========

An open source project to keep track of cryptocurrency finances, based on an internal prototype.

*NOTE* This is an experimental Git clone of the parent Subversion repository hosted on Google Code: http://code.google.com/p/openclerk/

List of features that present in the first release, now running at https://cryptfolio.com:

* Easy-to-use web interface based in PHP
* Create free accounts through OpenID
* Support for multiple cryptocurrencies (BTC, LTC, NMC)
* Support for multiple fiat currencies (USD, NZD)
* Support for offline wallets
* Support for online wallets
	* Mt.Gox
	* BTC-e
	* Vircurex
	* Litecoin Global
	* BTC Trading Co.
	* Generic API
* Support for multiple exchanges
	* Mt.Gox
	* BTC-e
	* Vircurex
	* BitNZ
* User-defined summary home page with configurable graphs and level of detail
* External API summary page
* Premium accounts to support site hosting and access more frequently updated data and advanced reports

More information: http://openclerk.org

## Installing

Openclerk is built and deployed with a rich assortment of web technologies.

* Install ruby, NodeJS, npm and composer.
* `gem install sass`
* `npm install`
* `composer install`
* Finally, `grunt serve` to build everything and watch for changes

## Extending

If you want to have an openclerk base (like CryptFolio), you can place your changes into a new `config/` directory,
and the build script (and [eventually](http://redmine.jevon.org/issues/132) config scripts and template scripts)
will include these files as necessary. Files supported:

```
config/site/css/*.scss -> site/styles/*.css
config/site/img/config/* -> site/img/config/*
config/site/img/favicon.ico -> site/favicon.ico
```
