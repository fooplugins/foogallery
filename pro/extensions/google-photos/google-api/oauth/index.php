<?php
// Basic Example
// include your composer dependencies
require_once 'vendor/autoload.php';

// Your redirect URI can be any registered URI, but in this example
// we redirect back to this same page
$redirect_uri = 'http://localhost/php/google-api-php-client--PHP7.4/oauth.php';
$googlephotos_client_secret = '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';

$client = new Google\Client();
$client->setClientId('90827584789-ghg88pdttme6b9iqevh4mrb7kl40khhh.apps.googleusercontent.com');
$client->setClientSecret($googlephotos_client_secret);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    print_r($token);
}