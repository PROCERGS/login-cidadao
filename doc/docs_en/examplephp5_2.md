# Example using PHP 5.2

## Before you begin

Make sure you have all the data described [here](integration.md#basic_info) before following the tutorial.

### Dependencies

To connect the Citizen Login using PHP `5.2` use OAuth client provided by site [PHPClasses](http://www.phpclasses.org/oauth-api).
This implementation has the dependency class `http-client` also available on the website [PHPClasses](http://www.phpclasses.org/httpclient).

## Getting started

### Configuration file

First we create a configuration file `oauth_configuration.json` regarding the OAuth server we want to use. In this file, specify the version of the OAuth protocol, the address to authenticate with markers to make variable substitutions, and the address for the Access Token:

``` js
// oauth_configuration.json
{
    "servers":
    {
        // WARNING: You must enter this name in the next step, em $client->server
        "Meu":
        {
            "oauth_version": "2.0",
            "dialog_url": "https://meu.rs.gov.br/oauth/v2/auth?response_type=code&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&state={STATE}&scope={SCOPE}",
            "access_token_url": "https://meu.rs.gov.br/oauth/v2/token"
        }
    }
}
```

### Configuring the client OAuth

First we instantiate the class `oauth_client_class`:

``` php
$client = new oauth_client_class();
//Here we specify the server name that we will use. This loads the extra settings OAuth client
$client->server = 'Meu';
//put one of our addresses that authorized the Citizen Login to return data
$client->redirect_uri = '';
//put our public key here
$client->client_id = '';
//put our private key here
$client->client_secret = '';
//put the list of desired scopes * separated by spaces *
$client->scope = '';
```

By default, the configuration file must be placed in the same directory as your script is running, but you can change that directory using the following attribute:

``` php
$client->configuration_file='';
```

### Making the magic happen

With the completed data, we can initialize the instance:

``` php
// With the completed data, we can initialize the instance
if (($success = $client->Initialize())) {
    // here it will process the data from $ _REQUEST to find out if needs to perform authorization for identity management and / or request an Access Token
    if (($success = $client->Process())) {
        if (strlen($client->authorization_error)) {
            $client->error = $client->authorization_error;
            $success = false;
        } elseif (strlen($client->access_token)) {
            // when we get the Access Token can take the data
            $success = $client->CallAPI('https://meu.rs.gov.br/api/v1/person', 'GET', array(), array(
                'FailOnAccessError' => true
            ), $user);
        }
    }
    // here it returns the status of the operations performed by the instance
    $success = $client->Finalize($success);
}
// if the class need to end the execution of this page, for example, when the identities manager returns data for that page, after seeking authorization
if ($client->exit) {
    exit();
}
```

Once obtained the Access Token can get the user data through the variable `$ user` that will be populated with the information requested on the property` scope`.

[Back to index](index.md)
