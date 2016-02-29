REST API Examples
=================

Sending Notifications
---------------------

Sending notifications through Citizen Login is as easy as sending an OAuth 2 authenticated POST request.

### Authentication

You can authenticate either via Client Credentials or using directly a person's Access Token. If you are sending more than one notification and it's been some time after the Access Token was issued then using Client Credentials is highly recommended as being usually a better idea since you'll save on your API quota by not having to request new Access Tokens for several people.

### Sending a Notification

To create a notification just send a POST request to our Notification API authenticating it with an OAuth 2 Access Token and pass at least the following parameters:

  * title: the notification's title
  * shortText: a brief description
  * person: this is the Unique ID of the user receiving the notification
  * sender: this is the ID of your OAuth Client
  * category: the notification's Category
  * placeholders: the notification's Placeholders

An example POST request would look like this:

```
POST /api/v1/person/notification.json HTTP/1.1
Host: meu.rs.gov.br
Content-Type: application/x-www-form-urlencoded
Authorization: Bearer M2M5YjgzNj...ZDg4OTJiNA

title=My+Nice+Title&shortText=This+is+the+notification%27s+brief+description&person=23521&sender=42&category=953&placeholders%5Bname%5D=Fulano+de+Tal&placeholders%5BextraInfo%5D=Something+here
```

In this example we are sending the following data:

``` js
{
	"title": "My Nice Title",
	"shortText": "This is the notification's brief description",
	"person": 23521,
	"sender": 42,
	"category": 953,
	"placeholders": {
		"name": "Fulano de Tal",
		"extraInfo": "Something here"
	}
}
```

As a response, you'll get a 201 HTTP status code as any RESTful API should, but as a courtesy we also return the ID of the newly created notification like in this example:

``` js
{
	"id": 654
}
```

### Receiving notification of readback

To receive a warning that the recipient of the notification read the message in the notification you must add the parameter `callbackUrl`:

```js
{
	...
	"callbackUrl" : "https://mysite/"
	...
}
```


If you were informed in a URL parameter `callbackUrl` in the notification, at the time user to read the notification will be sending a request to that address.
The request is POST type and return two parameters, the parameter `data` and the parameter `signature`.
The parameter 'data` is a serialized data structure in JSON format. In this structure we have the following data:

```js
{
	//the unique notification identifier
	'id': 1,
	//the unique user identifier
	'person_id': 1,
	//the date and time of the reading formatted in Unix timestamp
    'read_date': 1272509157
}
```

The parameter `signature` is a signature `HMAC` using the `sha256` on the information contained in the parameter `data`. The password used to make this signature is the private key (or secret) of Client OAuth. Thus, it is possible to ensure the authenticity of the message sent.

[Back to REST API Introduction](restApiIntro.md)

[Back to index](index.md)
