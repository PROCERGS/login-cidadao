REST API
========

In this document you'll find the basic information to get started with our REST API.

A better description and documentation on the available resources can be found at `http://<YOUR CITIZEN LOGIN DOMAIN>/api/doc/`.

Authentication
--------------

Our REST API uses OAuth 2 for authentication by use of an Access Token. You can read more [here (RFC 6749)](http://tools.ietf.org/html/rfc6749) and [here (RFC 6750)](http://tools.ietf.org/html/rfc6750).

### Getting an Access Token

<a name="refreshTokenNote"></a>**VERY IMPORTANT: What to do after receiving a token response**

This is **VERY IMPORTANT**: Every time you receive a token response, regardless of the grant type used, you'll be given the Access Token, obviously, but also a refresh token and an expiration time. You **HAVE TO** store this information so that you can request a new Access Token when the one you received expires. If you do not do that you'll depend on user interaction to get another Access Token one which is clearly *very bad* when you are in the middle of a cron job, for example.

You may, eventually, be **punished** by requesting new Access Tokens when you should actually be **renewing** the ones you've already got.

In addition to the normal process of obtaining an Access Token upon user authorization through the `authorization_code` grant type, which will give you an user-specific token, you can get an OAuth Client-specific Access Token using the Client Credentials grant type. This is specially useful when making several API calls to many different users that have already authorized your application.

#### Client Credentials

Client Credentials is just a way to acquire an Access Token as an OAuth Client instead of as an user.

Naturally it'll depend on how your OAuth 2 library is implemented, but basically at the first time, when you don't have an Access Token yet, it'll make a POST request to the tokens endpoint (`/oauth/v2/token`) with the following parameters:

  * grant_type: must be `client_credentials`
  * client_id: your public key
  * client_secret: your private key (a.k.a. secret)

The response will be something like this:

``` js
{
    "access_token": "YjcxOWZkNj...NDIxYTMzNg",
    "expires_in": 3600,
    "token_type": "bearer",
    "scope": "public_profile ...",
    "refresh_token": "MGE1OTQ4Yz...NDYyMTc1YQ"
}
```

After that please remember to store the **Refresh Token** as instructed [here](#refreshTokenNote).

#### Refresh Token

An Access Token can be obtained using a Refresh Token by sending a POST request to the tokens endpoint `/oauth/v2/token` with the following parameters:

  * grant_type: must be `refresh_token`
  * client_id: your public key
  * client_secret: your private key (a.k.a. secret)
  * refresh_token: your refresh token

And as a response you should get:

``` js
{
    "access_token": "OTk2OTM3OT...ZDAzNGNkMg",
    "expires_in": 3600,
    "token_type": "bearer",
    "scope": "public_profile ...",
    "refresh_token": "ZGY4ZDJiNm...ZGI1NzRlNw"
}
```

At this point your old Refresh Token cannot be used anymore, thus you must store the newly received one just as you would when asking for the Access Token at the first time ([important note](#refreshTokenNote)).

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

See Also
--------

[Sending Notifications](sendingNotifications.md)

[Back to index](index.md)
