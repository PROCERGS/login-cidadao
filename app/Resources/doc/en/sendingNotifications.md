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
