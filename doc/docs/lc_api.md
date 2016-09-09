REST API
========

Nesse documento você encontra informações básicas para usar nossa REST API.

Uma melhor descrição e documentação dos recursos disponíveis na API podem ser encontrados em `http://<DOMÍNIO DO LOGIN CIDADÃO>/api/doc/`.

Autenticação
------------

Nossa REST API usa OAuth 2 para autenticação através do uso de Access Tokens. Você pode ler mais sobre isso [aqui (RFC 6749)](http://tools.ietf.org/html/rfc6749) e [aqui (RFC 6750)](http://tools.ietf.org/html/rfc6750).

### Obtendo um Access Token

<a name="refreshTokenNote"></a>**MUITO IMPORTANTE: O que fazer ao receber uma resposta de token**

Isto é **MUITO IMPORTANTE**: Toda vez que você receber uma resposta a uma solicitação de token, independentemente do grant type utilizado, você receberá o Access Token, obviamente, mas também um Refresh Token e um tempo de expiração do Access Token. Você **PRECISA** armazenar essas informações para que consiga solicitar um novo Access Token quando o que você recebeu expirar. Se você não fizer isso, dependerá de interação do usuário para receber um novo Access Token, o que é claramente *péssimo* quando se está no meio de um cron job, por exemplo.

Você pode, eventualmente, ser **punido** por solicitar novos Access Tokens em vez de renovar os que você já possui.

Além do processo normal de obtenção de Access Token através do grant type `authorization_code`, que lhe retorna um token específico para um usuário, você pode receber um token relativo ao seu Client OAuth através do uso de Client Credentials com o grant type `client_credentials`. Isso é especialmente útil quando se está fazendo diversas chamadas à API para vários usuários diferentes que já autorizaram sua aplicação.

#### Client Credentials

Client Credentials é uma forma de obter um Access Token relativo ao seu próprio Client OAuth, em vez de um referente a um usuário.

Naturalmente seu uso dependerá de como a sua biblioteca OAuth 2 foi implementada, mas basicamente ela fará, na primeira vez, quando você ainda não tem um Access Token, uma requisição POST para o endpoint de tokens (`/oauth/v2/token`) com os seguintes parâmetros:

  * grant_type: precisa ser `client_credentials`
  * client_id: sua chave pública
  * client_secret: sua chave privada (também conhecida como secret/segredo)

A resposta deve ser algo como:

``` js
{
    "access_token": "YjcxOWZkNj...NDIxYTMzNg",
    "expires_in": 3600,
    "token_type": "bearer",
    "scope": "public_profile ...",
    "refresh_token": "MGE1OTQ4Yz...NDYyMTc1YQ"
}
```

Depois disso, por favor lembre-se de armazenar o **Refresh Token** recebido conforme instruído [aqui](#refreshTokenNote).

#### Refresh Token

Um Access Token pode ser obtido usando um Refresh Token enviando uma requisição POST ao endpoint de tokens `/oauth/v2/token` conforme os parâmetros:

  * grant_type: precisa ser `refresh_token`
  * client_id: sua chave pública
  * client_secret: sua chave privada (também conhecida como secret/segredo)
  * refresh_token: seu refresh token

E como resposta você deve receber:

``` js
{
    "access_token": "OTk2OTM3OT...ZDAzNGNkMg",
    "expires_in": 3600,
    "token_type": "bearer",
    "scope": "public_profile ...",
    "refresh_token": "ZGY4ZDJiNm...ZGI1NzRlNw"
}
```

Nesse momento seu antigo Refresh Token não pode mais ser usado, portanto você deve armazenar o novo token recebido da mesma maneira que faria ao solicitar o Access Token pela primeira vez ([nota importante](#refreshTokenNote)).

### Exemplos

A seguir você encontrará exemplos dos dois métodos mais comuns de acessar uma API protegida por OAuth 2.

#### Usando o campo Authorization do Header da Requisição

```
GET /api/v1/person.json HTTP/1.1
Host: meu.rs.gov.br
Authorization: Bearer YTY4ZDA0M2...YTU3ZjI3Ng
```

#### Usando um parâmetro na URI

```
GET /api/v1/person.json?access_token=YTY4ZDA0M2...YTU3ZjI3Ng HTTP/1.1
Host: meu.rs.gov.br
```

Veja também
-----------

[Enviando Notificações](lc_api_sending_notifications.md)

