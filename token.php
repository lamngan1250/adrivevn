<?php
include "config.php";

global $credentials_file;
$client = new Google_Client();
$client->setApplicationName('Google Drive API PHP Quickstart');
$client->setScopes(Google_Service_Drive::DRIVE);
$client->setAuthConfig(json_decode(getSettings('credentials'), true));
$client->setAccessType('offline');
$authUrl = $client->createAuthUrl();
if (!isset($_POST['code'])) {
        echo ("Open the following link in your browser: <a href='$authUrl' target='_blank'>$authUrl</a><br/>");
        echo "<form method='POST'>Code: <input name='code' type='text'></input> <button type='submit'>Verify</button></form>";
    /*$client->setAccessToken($accessToken);
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode(array_merge($accessToken, $client->getAccessToken())));
    }
    return $client;*/
} else {
        $authCode = trim($_POST['code']);
        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }
  
        echo setSettings('DriveToken', $accessToken);
}
