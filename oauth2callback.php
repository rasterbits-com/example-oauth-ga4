<?php

// Load the Google API PHP Client Library. C:\xampp\htdocs\ga4\nico\oauth2callback.php
require_once  './vendor/autoload.php';

// Start a session to persist credentials.
session_start();

// Create the client object and set the authorization configuration
// from the client_secrets.json you downloaded from the Developers Console.
$client = new Google_Client();
//$client->setAuthConfig('/client_secrets.json');
$client->setAuthConfig(__DIR__ . '/client_secrets.json');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

// create client.txt file with client data 
$archivo_print = fopen("client.txt", "w+b");    // Abrir el archivo, creándolo si no existe
fwrite($archivo_print, $client);
fflush($archivo_print); // Fuerza a que se escriban los datos pendientes en el buffer:
fclose($archivo_print);

// Handle authorization flow from the server.
if (! isset($_GET['code'])) {
  //
  $client->setAccessType('offline');
  $client->setApprovalPrompt('force');
  //
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $_SESSION['refresh_token'] = $client->getRefreshToken();
  $_SESSION['client_id'] = $client->getClientId();
  $_SESSION['client_secret'] = $client->getClientSecret();

  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
