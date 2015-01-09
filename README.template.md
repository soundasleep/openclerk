openclerk
=========

An open source project to keep track of cryptocurrency finances, based on an internal prototype.

Requires PHP 5.4+ and uses a number of awesome child components:

* [openclerk/db](https://github.com/openclerk/db) for database abstraction
* [openclerk/emails](https://github.com/openclerk/emails) for email management
* [openclerk/config](https://github.com/openclerk/config) for configuration management
* [openclerk/events](https://github.com/openclerk/events) for server-side event management
* [openclerk/routing](https://github.com/openclerk/routing) and [openclerk/templates](https://github.com/openclerk/templates) for page routing and templating
* [openclerk/currencies](https://github.com/openclerk/currencies) for abstract currency definitions
* [openclerk/cryptocurrencies](https://github.com/openclerk/cryptocurrencies) for cryptocurrency definitions
* [soundasleep/component-discovery](https://github.com/soundasleep/component-discovery) and
  [soundasleep/asset-discovery](https://github.com/soundasleep/asset-discovery) for runtime component and asset discovery, enabling custom extensions

## Features

List of features that are also running at https://cryptfolio.com:

* Easy-to-use web interface based in PHP
* Create free accounts through OpenID or passwords
* Support for multiple currencies
  * Cryptocurrencies ({$crypto_currencies_inline})
  * Fiat currencies ({$fiat_currencies_inline})
  * Commodity currencies ({$commodity_currencies_inline})
* Support for exchange wallets
{$exchange_wallets_list}
* Support for currency exchanges
{$exchange_list}
  * Market averages
* Support for mining pools and miner hashrates
{$mining_pools_list}
* Support for securities exchanges
{$securities_list}
* User-defined reports page with configurable graphs and level of detail
* User-defined notifications on exchanges, balances and hashrates
* Plenty of helpful tools
  * Historical data for exchanges
  * External API status page
  * Cryptocurrency calculator widget, e.g.: https://cryptfolio.com/calculator
  * Comprehensive administrator interface
* Premium accounts to support site hosting and access more frequently updated data and advanced reports

More information: http://openclerk.org

## Extending

If you want to have an openclerk base (like CryptFolio), you can place your changes into a new `config/` directory,
and the build script (and [eventually](http://redmine.jevon.org/issues/132) config scripts and template scripts)
will include these files as necessary. Files supported:

```
config/site/css/*.scss -> site/styles/*.css
config/site/img/config/* -> site/img/config/*
config/site/img/favicon.ico -> site/favicon.ico
config/templates/*.php -> site/templates/*.php
```
