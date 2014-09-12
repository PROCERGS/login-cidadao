<?php
/*
 *  Get the http.php file from http://www.phpclasses.org/httpclient
 *  Get the oauth_client.php file from http://www.phpclasses.org/oauth-api
 */
require ('http.php');
require ('oauth_client.php');

$client = new oauth_client_class();
$client->debug = 1;
$client->debug_http = 1;
$client->server = 'Meu';
$client->redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . dirname(strtok($_SERVER['REQUEST_URI'], '?')) .  basename(__FILE__);

$client->client_id = '';
$application_line = __LINE__ - 1;
$client->client_secret = '';

if (strlen($client->client_id) == 0 || strlen($client->client_secret) == 0) {
    die('Please go to Twitter Apps page https://meu.rs.gov.br/dev , ' . 'create an application, and in the line ' . $application_line . ' set the client_id to Consumer key and client_secret with Consumer secret. ' . 'The Callback URL must be ' . $client->redirect_uri . ' If you want to post to ' . 'the user timeline, make sure the application you create has write permissions');
}
/*
 * API permissions
 */
$client->scope = 'id username full_name name cpf birthdate email city picture public_profile voter_registration badges country uf city adress adress_number adress_complement rgs';
if (($success = $client->Initialize())) {
    if (($success = $client->Process())) {
        if (strlen($client->authorization_error)) {
            $client->error = $client->authorization_error;
            $success = false;
        } elseif (strlen($client->access_token)) {
            $success = $client->CallAPI('https://meu.rs.gov.br/api/v1/person', 'GET', array(), array(
                'FailOnAccessError' => true
            ), $user);
        }
    }
    $success = $client->Finalize($success);
}
if ($client->exit) {
    exit();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>OAuth client results</title>
</head>
<body>
<?php
if ($success) {
    echo '<h1>', HtmlSpecialChars($user->name), ' you have logged in successfully with Oauth!</h1>';
    echo '<pre>', HtmlSpecialChars(print_r($user, 1)), '</pre>';
} else {
    echo HtmlSpecialChars($client->error);
}
?>
</body>
</html>
