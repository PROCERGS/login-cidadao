Login Cidadão
=============

[![Build Status](https://travis-ci.org/redelivre/login-cidadao.svg?branch=master)](https://travis-ci.org/redelivre/login-cidadao)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/redelivre/login-cidadao/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/redelivre/login-cidadao/?branch=master)
[![Join the chat at https://telegram.me/IdentidadeDigital](https://patrolavia.github.io/telegram-badge/chat.png)](https://telegram.me/IdentidadeDigital)
[![Receive updates at https://telegram.me/logincidadao](https://patrolavia.github.io/telegram-badge/follow.png)](https://telegram.me/logincidadao)

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

## Docs (Portuguese)

  - [Sobre o Login Cidadão](doc/about.md)
  - **Instalação**
    - [Instruções detalhadas](doc/deploy.md)
    - [Passo-a-passo para Debian](doc/cookbook/deploy_debian_os.md)
    - [Troubleshooting](doc/deploy_troubleshooting.md)
  - **Configuração**
    - [Arquivo parameters.yml](doc/config_parameters.md)
  - **Gerenciamento da Instalação**
    - [Atualizando o Login Cidadão](doc/maintenance.md)
    - [Gerenciamento de Usuários](doc/maintenance_user_management.md)
    - [Comandos do Symfony](doc/maintenance_symfony_commands.md)
  - **Uso do Login Cidadão**
    - [Usando OpenID Connect](doc/cookbook/using_openid_connect.md)
    - [Deslogando usuários "remotamente"](doc/cookbook/using_logout.md)
    - [Documentação da API](doc/api.md)
    - [Migração/Importação de Usuários](doc/cookbook/import_users.md)
  
Você pode utilizar o visualizador em `doc/index.html` para exibir a documentação com uma formatação amigável
em um browser iniciando um servidor built-in do PHP com o seguinte comando:

```
composer lc-docs <porta desejada>
```
