Login Cidadão
=============

[![Build Status](https://travis-ci.org/redelivre/login-cidadao.svg?branch=master)](https://travis-ci.org/redelivre/login-cidadao)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/redelivre/login-cidadao/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/redelivre/login-cidadao/?branch=master)
[![Join the chat at https://gitter.im/PROCERGS/login-cidadao](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/PROCERGS/login-cidadao?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Requirements

Running an Identity Provider is not an easy task.
If you plan to maintain one yourself, you MUST:

**FULLY understand**:
 * [RFC 7231 - Response Status Codes](https://tools.ietf.org/html/rfc7231#section-6)
 * [OpenID Connect Core 1.0 - TLS Requirements](http://openid.net/specs/openid-connect-core-1_0.html#TLSRequirements)
 * [RFC 6749 - Ensuring Endpoint Authenticity](http://tools.ietf.org/html/rfc6749#section-10.9)
 * How to debug a REST API
 * How to debug PHP code
 * You SHOULD NOT be using plain OAuth 2.0

Have at least a **very good** understanding of:
 * [OpenID Connect Core 1.0](http://openid.net/specs/openid-connect-core-1_0.html)
 * [OpenID Connect Discovery 1.0](http://openid.net/specs/openid-connect-discovery-1_0.html)
 * [OpenID Connect Dynamic Client Registration 1.0](http://openid.net/specs/openid-connect-registration-1_0.html)
 * [RFC 6749](http://tools.ietf.org/html/rfc6749)

To perform customizations you **MUST** have a good understanding of:
 * [The Symfony Book](https://symfony.com/doc/2.8/book/index.html)
 * [The Symfony Cookbook](https://symfony.com/doc/2.8/cookbook/index.html)

If you fail to comply with the aforementioned requirements you and your users are very likely going to get hurt

## OS Dependencies

 * PHP >=5.4
 * [composer](https://getcomposer.org)
 * [node.js](http://nvm.sh)
 * [memcached](https://memcached.org/)

### PHP Extensions
  * php5-curl
  * php5-intl
  * php5-mysql or php5-pgsql or your preferred driver
  * php5-memcache (you can use php5-memcached instead, just remember to change the `Memcache` classes to `Memcached`)

### System Configuration
  * php timezone (example: `date.timezone = America/Sao_Paulo`)
  * write permission to `app/cache`, `app/logs` and `web/uploads`

## Docs

[ Read the docs ](app/Resources/doc/index.md)

## Setup (Development)

### Linux
#### Requirements
 * Sudoer user
 * PHP CLI
 * ACL-enabled filesystem
 * Composer

#### Before you start
It's highly recommended to create your `app/config/parameters.yml` before installing to avoid database connection problems.

You can start by using `app/config/parameters.yml.dist` as a template by simply copying it to the same folder but naming it as `parameters.yml`, then edit the default values.

#### Running the script
Check if your environment meets Symfony's prerequesites:
``` bash
php app/check.php
```

Just execute the `install.sh` script and follow instructions in case of errors or warnings. Then run:
``` bash
php app/console server:run
```
Browse to `http://localhost:8000`

### Vagrant

#### Requirements
* virtualbox
* [vagrant](https://www.vagrantup.com/)
* vagrant plugin [vagrant-vbguest](https://github.com/dotless-de/vagrant-vbguest) for port forward

#### Before you start
It's highly recommended to create your `app/config/parameters.yml` before installing to avoid database connection problems.

You can start by using `app/config/parameters.yml.vagrant` as a template by simply copying it to the same folder but naming it as `parameters.yml`, then edit the default values. Do not edit database values if you want to use the default vm database.

#### Run
``` bash
$ vagrant up
```

### General Steps for Installation

[Instructions in Brazilian Portuguese](doc/docs/como-instalar.md)
