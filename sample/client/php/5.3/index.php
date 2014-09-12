<?php

require_once 'vendor/autoload.php';
require_once 'config.inc.php';

$apiUri = apiUri;
$clientConfig = new \fkooman\OAuth\Client\ClientConfig(array(
    "authorize_endpoint" => authorize_endpoint,
    "client_id" => client_id,
    "client_secret" => client_secret,
    "token_endpoint" => token_endpoint,
    "redirect_uri" => redirect_uri
));

$tokenStorage = new \fkooman\OAuth\Client\SessionStorage();
$httpClient = new \Guzzle\Http\Client();
$api = new fkooman\OAuth\Client\Api("foo", $clientConfig, $tokenStorage, $httpClient);

//$context = new \fkooman\OAuth\Client\Context("myapp", array("id", "username", "full_name", "name", "cpf", "birthdate","email","city","picture","public_profile","voter_registration","badges","country"));
$context = new \fkooman\OAuth\Client\Context("john.doe@example.org", array("id"));

$accessToken = $api->getAccessToken($context);
if (false === $accessToken && $_GET['authorize'] == 1) {
    /* no valid access token available, go to authorization server */
    header("HTTP/1.1 302 Found");
    header("Location: " . $api->getAuthorizeUri($context));
    exit;
}elseif(false === $accessToken){
	echo "<a href='index.php?authorize=1'>Logar com o Login do Cidadao</a>";
	exit;
}

try {
    $client = new \Guzzle\Http\Client();
    $bearerAuth = new \fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($accessToken->getAccessToken());
    $client->addSubscriber($bearerAuth);
    $response = $client->get($apiUri)->send();
    header("Content-Type: application/json");
    $json= $response->getBody();
    $json = json_decode($json, true);
    if (false !== $json) {
        echo $json['username'];
    }
} catch (\fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException $e) {
    if ("invalid_token" === $e->getBearerReason()) {
        // the token we used was invalid, possibly revoked, we throw it away
        $api->deleteAccessToken($context);
        $api->deleteRefreshToken($context);
        /* no valid access token available, go to authorization server */
        header("HTTP/1.1 302 Found");
        header("Location: " . $api->getAuthorizeUri($context));
        exit;
    }
    throw $e;
} catch (\Exception $e) {
    die(sprintf('ERROR: %s', $e->getMessage()));
}
