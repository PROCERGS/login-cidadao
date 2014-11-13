"Remote" Logout
===============

Remote Logout is the name of the feature that allows for an OAuth Client, properly authorized by the user, to generate a Logout Key. The Logout Key will then be used to log this user out by accessing a link.

Steps
-----

1. The OAuth Client (a.k.a. Service ou 3rd Party Application) requests an Access Token if one isn't available through the Client Credentials grant type. ([Learn more](http://aaronparecki.com/articles/2012/07/29/1/oauth2-simplified))
2. OAuth Client uses it's Access Token to request a Logout Key for a person that has already authorized such client with the `logout` scope.
3. OAuth Client receives the Logout Key then redirects the user to the logout URL passing the just-obtained key.
4. User is logged out of Citizen Login.

**Note:** Each Logout Key has a 5 minutes lifespan and can be used only once.

Steps Detailed
--------------

### Obtaining the Access Token

This step is specific to your programming language and OAuth library so you'll have to learn how to use it yourself.

### Requesting the Logout Key

The Logout Key is obtained by calling the Citizen Login API through `GET /api/v1/person/{personId}/logout-key.json` and authenticating using your application's Access Token.

#### Example:

Request:
```
GET /api/v1/person/1/logout-key.json
Authorization: Bearer ZTQzNWMzMWM...TY4M2JkNTdhZGFhMTFiZmYwZA
```

Response:
``` json
{
    "key": "7cfe184...4d71c6e",
    "url": "http://meu.rs.gov.br/logout/if-not-remembered/7cfe184...4d71c6e"
}
```

Note that for convinience we already return the full URL that you'll have to redirect the user to or open in a hidden iframe.

### Redirect the user

Having the Logout Key and URL you can either redirect the user to that URL or open it in a hidden iframe.

[Back to index](index.md)
