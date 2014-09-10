openclerk
=========

An open source project to keep track of cryptocurrency finances, based on an internal prototype.

*NOTE* This is an experimental Git clone of the parent Subversion repository hosted on Google Code: http://code.google.com/p/openclerk/

## Features

List of features that are also running at https://cryptfolio.com:

* Easy-to-use web interface based in PHP
* Create free accounts through OpenID or passwords
* Support for multiple currencies
  * Cryptocurrencies (BTC, LTC, NMC, PPC, FTC, NVC, XPM, TRC, DOGE, MEC, XRP, DGC, WDC, IXC, VTC, NET, HBN, BC, DRK, VRC, NXT)
  * Fiat currencies (USD, GBP, EUR, CAD, AUD, NZD, CNY, PLN, ILS, KRW, SGD, DKK, INR)
  * Commodity currencies (GHS)
* Support for exchange wallets
  * ANXPRO
  * Bit2c
  * BitMarket.pl
  * Bitstamp
  * Bittrex
  * BTC-e
  * CEX.io
  * Coinbase
  * Crypto-Trade
  * Cryptsy
  * Justcoin
  * Kraken
  * Mt.Gox
  * Poloniex
  * Vault of Satoshi
  * Vircurex
* Support for currency exchanges
  * ANXPRO
  * Bit2c
  * Bitcurex
  * BitMarket.pl
  * BitNZ
  * Bitstamp
  * Bittrex
  * BTC China
  * BTC-e
  * CEX.io
  * Coinbase
  * Coins-E
  * Crypto-Trade
  * Cryptsy
  * Justcoin
  * Kraken
  * itBit
  * MintPal
  * Mt.Gox
  * Poloniex
  * TheMoneyConverter
  * Vault of Satoshi
  * Vircurex
  * VirtEx
  * Market averages
* Support for mining pools and miner hashrates
  * 50BTC
  * b(e^5)r.org
  * BitMinter
  * BTC Guild
  * CoinHuntr
  * CryptoPools
  * Cryptotroll
  * d2
  * dedicatedpool.com
  * Ecoining
  * Eligius
  * Elitist Jerks
  * GHash.io
  * Give Me Coins
  * HashFaster
  * ltc.kattare.com
  * nvc.khore.org
  * litecoinpool.org
  * LiteGuardian
  * Litepool
  * LTCMine.ru
  * MiningPool.co
  * Multipool
  * MuPool
  * Nut2Pools
  * Ozcoin
  * Pool-x.eu
  * RapidHash
  * ScryptGuild
  * scryptpools.com
  * Slush's pool
  * TeamDoge
  * TripleMining
  * WeMineFTC
  * WeMineLTC
  * ypool.net
* Support for securities exchanges
  * 796 Xchange
  * BTCInve
  * Crypto-Trade
  * Cryptostocks
  * Havelock Investments
  * Litecoininvest
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
