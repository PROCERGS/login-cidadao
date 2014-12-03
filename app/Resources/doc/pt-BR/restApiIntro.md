REST API
========

Nesse documento você encontra informações básicas para usar nossa REST API.

Uma melhor descrição e documentação dos recursos disponíveis na API podem ser encontrados em `http://<YOUR CITIZEN LOGIN DOMAIN>/api/doc/`.

Autenticação
------------

Nossa REST API usa OAuth 2 para autenticação através do uso de Access Tokens. Você pode ler mais sobre isso [aqui (RFC 6749)](http://tools.ietf.org/html/rfc6749) e [aqui (RFC 6750)](http://tools.ietf.org/html/rfc6750).

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
