REST API
========

In this document you'll find the basic information to get started with our REST API.

A better description and documentation on the available resources can be found at `http://<YOUR CITIZEN LOGIN DOMAIN>/api/doc/`.

Authentication
--------------

Our REST API uses OAuth 2 for authentication by use of an Access Token. You can read more [here (RFC 6749)](http://tools.ietf.org/html/rfc6749) and [here (RFC 6750)](http://tools.ietf.org/html/rfc6750).

### Examples

Below you'll find the two most common methods of accessing an OAuth 2 protected API.

#### Using the Authorization Request Header Field

```
GET /api/v1/person.json HTTP/1.1
Host: meu.rs.gov.br
Authorization: Bearer YTY4ZDA0M2...YTU3ZjI3Ng
```

#### Using an URI Query Parameter

```
GET /api/v1/person.json?access_token=YTY4ZDA0M2...YTU3ZjI3Ng HTTP/1.1
Host: meu.rs.gov.br
```
