Exemplo usando OpenID Connect
=============================

Nesse documento você encontra um exemplo simplificado de implementação de um
cliente OpenID Connect em PHP. Caso você pretenda utilizar outras linguagens,
consulte a [lista de bibliotecas que implementam OpenID Connect](http://openid.net/developers/libraries/)
para verificar se existe uma na sua linguagem preferida que implemente o papel de
*Relying Party*.

Requisitos
----------

Para este exemplo utilizaremos a biblioteca [`jumbojett/openid-connect-php`](https://github.com/jumbojett/OpenID-Connect-PHP)
por ser uma implementação bastante minimalista mas que já suporta a especificação
[OpenID Connect Dynamic Client Registration 1.0](http://openid.net/specs/openid-connect-registration-1_0.html).

Você vai precisar de:

  1. *PHP 5.2* ou superior;
  2. Extensão *cURL*;
  3. Extensão *JSON*;
  4. *Permissão de escrita* em um diretório;
  5. *Login Cidadão* ou outro provedor de identidade compatível com OpenID Connect.

Exemplo
-------

A utilização dessa biblioteca é muito simples, basta instalar a biblioteca que
será usada.

    # composer require jumbojett/openid-connect-php:0.1.*

Em seguida, crie um arquivo `index.php` com o seguinte conteúdo:

``` php
<?php

// Carregamos as dependências
require 'vendor/autoload.php';

// Informe o endereço de seu Login Cidadão
// Idealmente esse valor deve ser informado pelo usuário para que seja usado
// o modo Federado do OpenID Connect.
$oidcProvider = 'https://id.provider.com';

// Carregamos as informações do cliente, caso existam.
// Se você permitir que o usuário digite seu provedor de identidade,
// lembre-se de que para cada provedor de identidade seu Client deve
// registrar-se uma única vez e você deve armazenar o ID e Secret para cada
// provedor de identidade conhecido.
$clientMetadata = json_decode(file_get_contents('metadata.json'));

// Caso não tenhamos o ID e Secret, significa que é necessário registrar-se
// ou você deverá preencher essas informações manualmente caso você já possua
// um Client cadastrado no Login Cidadão.
if (!$clientMetadata || !$clientMetadata->id || !$clientMetadata->secret) {
    $oidc = new OpenIDConnectClient($oidcProvider);
    $oidc->setClientName('My OIDC Client Example');
    $oidc->register();
    $clientMetadata = [
        'id' => $oidc->getClientID(),
        'secret' => $oidc->getClientSecret(),
    ];
    file_put_contents('metadata.json', json_encode($clientMetadata));
} else {
    $oidc = new OpenIDConnectClient($oidcProvider, $clientMetadata->id, $clientMetadata->secret);
}

try {
    // Efetuamos o processo de autenticação
    $oidc->authenticate();
    
    // Obtemos o primeiro nome do usuário logado
    $name = $oidc->requestUserInfo('given_name');

    echo "<p>Hello $name!</p>";
} catch (OpenIDConnectClientException $e) {
    echo $e->getMessage();
    echo '<p><a href="/">Try Again</a></p>';
}
```
