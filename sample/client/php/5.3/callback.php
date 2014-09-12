<?php

require_once 'vendor/autoload.php';
require_once 'config.inc.php';

$clientConfig = new \fkooman\OAuth\Client\ClientConfig(array(
    "authorize_endpoint" => authorize_endpoint,
    "client_id" => client_id,
    "client_secret" => client_secret,
    "token_endpoint" => token_endpoint,
    "redirect_uri" => redirect_uri
));

try {
    $tokenStorage = new \fkooman\OAuth\Client\SessionStorage();
    $httpClient = new \Guzzle\Http\Client();
    $cb = new \fkooman\OAuth\Client\Callback("foo", $clientConfig, $tokenStorage, $httpClient);
    $cb->handleCallback($_GET);

    header("HTTP/1.1 302 Found");
    header("Location: ". siteUrl);
    exit;
} catch (\fkooman\OAuth\Client\AuthorizeException $e) {
    // this exception is thrown by Callback when the OAuth server returns a
    // specific error message for the client, e.g.: the user did not authorize
    // the request
    die(sprintf("ERROR: %s, DESCRIPTION: %s", $e->getMessage(), $e->getDescription()));
} catch (\Exception $e) {
    // other error, these should never occur in the normal flow
    die(sprintf("ERROR: %s", $e->getMessage()));
}
