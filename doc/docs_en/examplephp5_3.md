# Example using PHP 5.3

## Before you begin

Make sure you have all the data described [here](integration.md#basic_info) before following the tutorial.

### Dependencies

To connect the Citizen Login using PHP `5.3` use OAuth client developed by [fkooman] (https://github.com/fkooman/php-oauth-client). Since we use PHP5.3 is possible to use `composer` to install the component and its dependancy. First, it is necessary to install [composer] (https://getcomposer.org/).
In `composer.json` just add the following configuration:

``` js
{
    "name": "fkooman/php-oauth-client-example", 
    "require": {
        "fkooman/guzzle-bearer-auth-plugin": "dev-master", 
        "fkooman/php-oauth-client": "dev-master"
    }
}
```

Using the command line, in the same directory that the file `composer.json` is possible to run the following command:` composer install --prefer-dist`. So the `composer` install the desired component and its dependencies.

## Getting started

### Configuration file

First we create a configuration file `config.ini` regarding the OAuth server we want to use. In this file, specify the address to authenticate, the address for the Access Token, the address to where the manager identities will return the data, the public key, the private key, the server name, the desired scopes and the address for user data:

```
[fkooman_client_config]
//address to authenticate
authorize_endpoint = "https://meu.rs.gov.br/oauth/v2/auth";
//address to require the Access Token
token_endpoint = "https://meu.rs.gov.br/oauth/v2/token";
//address to return the data
redirect_uri = "http://localhost/callback.php";
//public key
client_id = "";
//private key
client_secret = "";

[fkooman_api_config]
//name server
api_context = "Meu"
//scopes
api_scopes = "id username full_name cpf birthdate email city picture public_profile badges country state city addresses id_cards get_all_notifications notifications cep mobile";
//address to request user data
api_url = "https://meu.rs.gov.br/api/v1/person.json";
```

### Creating an authentication script

You must create a script that enables the User login. This script has to test whether the user is already authenticated. The moment the script detects that the user is authenticated, it to get the information about that user.
 
``` php
//first load the settings
$config = parse_ini_file('config.ini');
if (false === $config) {
    die('please you need to have a config.ini file. Make one based on config.ini.dist');
}
//with the settings you create a instance of the client settings
$clientConfig = new \fkooman\OAuth\Client\ClientConfig($config);

//we create an instance for storing the data used by Oauth
$tokenStorage = new \fkooman\OAuth\Client\SessionStorage();
//we create an instance to make the http communication
$httpClient = new \Guzzle\Http\Client();
//we create an instance to use the API of our identity management
$api = new fkooman\OAuth\Client\Api($config['api_context'], $clientConfig, $tokenStorage, $httpClient);
//we set the context of our API
$context = new \fkooman\OAuth\Client\Context($config['api_context'], explode(" ", $config['api_scopes']));
//check if we already have an Access Token
$accessToken = $api->getAccessToken($context);
if (false === $accessToken) {
	//if you do not have an Access Token
	//if we have a variable indicating that we want to authenticate
    if ($_GET['authorize'] == 1) {        
    	//send the User to the authorization screen of identity management
        header("HTTP/1.1 302 Found");
        header("Location: " . $api->getAuthorizeUri($context));
    } else {
    	//provide authentication for the user
        echo "<a href='index.php?authorize=1'>Logar com o Login do Cidadao</a>";
    }
    exit;
} else {
	//if we have the Access token we can call the API and get the data from it
    try {
        $client = new \Guzzle\Http\Client();
        $bearerAuth = new \fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($accessToken->getAccessToken());
        $client->addSubscriber($bearerAuth);
        $response = $client->get($config['api_url'])->send();
        header("Content-Type: application/json");
        //the return of the API is in JSON format, so we have to decode it
        $json = json_decode($response->getBody(), true);
        if (false !== $json) {
            print_r($json);
        } else {
            print_r($response->getBody());
        }
    } catch (\fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException $e) {
    	//if we have an Invalid token error, we play in this token out, try to log in again
        if ("invalid_token" === $e->getBearerReason()) {            
            $api->deleteAccessToken($context);
            $api->deleteRefreshToken($context);            
            header("HTTP/1.1 302 Found");
            header("Location: " . $api->getAuthorizeUri($context));
            exit;
        }
        throw $e;
    } catch (\Exception $e) {
        die(sprintf('ERROR: %s', $e->getMessage()));
    }    
}

}
```

### Creating a script that receives the return of identity management

It is also necessary to create a script that receives the identities manager return data. This script when receiving server data should refer to the authentication script

```php
//first load the settings
$config = parse_ini_file('config.ini');
if (false === $config) {
    die('please you need to have a config.ini file. Make one based on config.ini.dist');
}
//with the settings is created an instance of the client settings
$clientConfig = new \fkooman\OAuth\Client\ClientConfig($config);
try {
	//we create an instance for storing the data used by Oauth
    $tokenStorage = new \fkooman\OAuth\Client\SessionStorage();
    //we create an instance to make the http communication
    $httpClient = new \Guzzle\Http\Client();
    //we create an instance to deal with the return of identity management
    $cb = new \fkooman\OAuth\Client\Callback($config['api_context'], $clientConfig, $tokenStorage, $httpClient);    
    $cb->handleCallback($_GET);
    //we direct to the authentication script
    header("HTTP/1.1 302 Found");
    header("Location: index.php");
    exit;
} catch (\fkooman\OAuth\Client\AuthorizeException $e) {
    die(sprintf("ERROR: %s, DESCRIPTION: %s", $e->getMessage(), $e->getDescription()));
} catch (\Exception $e) {
    die(sprintf("ERROR: %s", $e->getMessage()));
}
```

With these two script it is possible to authenticate the user and retrieve their identity manager information

[Back to index](index.md)
