# Integration

## Introduction

This page describes how to make a basic integration with Login Citizen, an identity manager that uses the OAuth protocol 2.

Caso queira mais informações sobre OAuth 2, por favor, leia [ a página do OAuth 2 ](http://oauth.net/2/) and also [this post Aaron Parecki] (http://aaronparecki.com/articles/2012/07/29/1/oauth2-simplified).

OAuth 2 protocol is complex, so unless you know deeply both [RFC-6749] (http://tools.ietf.org/html/rfc6749) as the best security practices, use some implemented library and maintained by someone who knows.

## Before you begin

### <a name="basic_info"></a>Basic Information

The basic and fundamental information for the operation of the examples and the authentication process are the following:

 * URLs from Login Cidadão
   * URL Authorization. Example: `https://meu.rs.gov.br/oauth/v2/auth`
   * URL to request Access Token. Example: `https://meu.rs.gov.br/oauth/v2/token`
   * URL with the authenticated user data. Example: `https://meu.rs.gov.br/api/v1/person.json`
 * The set of scopes that will be accessed
 * Public key of the Client OAuth
 * Private key (or secret) Client OAuth
 * We need to specify the Return URL that identity management is authorized to return data.

Both Login Citizen URLs as the access keys must be obtained in Login Citizen installation you want to log in. While Return URLs depend on your Client OAuth..

### Scopes

You may ask how many and which scopes you want, however be aware that the more information you ask the user lower the probability of it authorize him to receive them, so just ask for the **absolutely necessary**.

The list of supported scopes can be found [here](scopes.md).

## Examples

No nosso branch `examples` demonstramos a integração utilizando `Java`, `PHP 5.2` e `PHP 5.3`.
Em todos os exemplos é necessário ter as [ Informações Básicas ](#basic_info).

To continue choose one of the following examples to explain how to use the authentication:

* [ Example using PHP 5.2 ](examplephp5_2.md)
* [ Example using PHP 5.3 ](examplephp5_3.md)
* [ Example using Java ](examplejava.md)

[ Back to index ](index.md)
