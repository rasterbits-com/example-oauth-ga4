

<?php
// [START analyticsdata_quickstart_oauth2]
require 'vendor/autoload.php';

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\ApiCore\ApiException;
use Google\Auth\OAuth2;


use Google\Analytics\Admin\V1alpha\Account;
use Google\Analytics\Admin\V1alpha\AnalyticsAdminServiceClient;
use Google\Analytics\Admin\V1alpha\ListAccountsResponse;

// Set up the client
$property_id = 'property_id'; // example '40536234';
session_start();

/*  original oauth2 */
putenv('GOOGLE_APPLICATION_CREDENTIALS=client_secrets.json');   

// Set authorization parameters.
$s = file_get_contents('./client_secrets.json');
$keys = json_decode($s);

$oauth = new OAuth2([
    'type' => 'authorized_user',
    'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
    'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
    'authorizationUri' => $keys->{'web'}->{'auth_uri'},
    'clientId' => $keys->{'web'}->{'client_id'},
    'clientSecret' => $keys->{'web'}->{'client_secret'},
    'redirectUri' => 'http://' . $_SERVER['HTTP_HOST']  . '/ga4/nico/',
]);


if (isset($_SESSION['access_token']) && $_SESSION['access_token']
    && isset($_SESSION['refresh_token']) && $_SESSION['refresh_token']) {

    $oauth->setAccessToken($_SESSION['access_token']);
    $oauth->setRefreshToken($_SESSION['refresh_token']);

    // example with property_id
    try {
        // Make an API call.
        $client = new BetaAnalyticsDataClient(['credentials' => $oauth]);
        $response = $client->runReport([
            'property' => 'properties/' . $property_id,
            'dateRanges' => [
                new DateRange([
                    'start_date' => '2020-03-31',
                    'end_date' => 'today',
                ]),
            ],
            'dimensions' => [new Dimension(
                [
                    'name' => 'city',
                ]
            ),
            ],
            'metrics' => [new Metric(
                [
                    'name' => 'activeUsers',
                ]
            )
            ]
        ]);

        // Print results of an API call.
        print 'Report result: <br />';

        foreach ($response->getRows() as $row) {
            print $row->getDimensionValues()[0]->getValue()
                . ' ' . $row->getMetricValues()[0]->getValue() . '<br />';
        }

        // example withouth property_id
        $accessToken = $oauth->getAccessToken();
        
        // list accounts
        print ' <br /> Listado de cuentas: <br />';
        
        $curl_accounts = curl_init();
        curl_setopt($curl_accounts, CURLOPT_URL, 'https://analyticsadmin.googleapis.com/v1beta/accounts');
        curl_setopt($curl_accounts, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_accounts, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        $res = curl_exec($curl_accounts);
        curl_close($curl_accounts);
        
        $data = json_decode($res, true);
        $accounts = $data['accounts'];

        foreach ($accounts as $account) {
            echo "Account ID: " . $account['name'] . PHP_EOL. "<br>";
            echo "Display Name: " . $account['displayName'] . PHP_EOL. "<br>";
            echo "CreateTime: " . $account['createTime'] . PHP_EOL. "<br>";
            echo PHP_EOL. "<br><br>";

            // list properties for account
            // example $filter = 'parent:accounts/76114252';

            $filter = 'parent:'. $account['name'] .  PHP_EOL;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://analyticsadmin.googleapis.com/v1beta/properties?filter=' . urlencode($filter));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            $properties = isset($data['properties']) ? $data['properties'] : [];

            foreach ($properties as $property) {
                print ' <br /> Listado de propiedades de cuenta:  ' . $account['name'] . ' <br />';
                echo "Property ID: " . $property['name'] .  PHP_EOL. "<br>";
                echo "Display Name: " . $property['displayName'] .  PHP_EOL. "<br>";
                echo "CreateTime: " . $property['createTime'] .  PHP_EOL. "<br>";
                echo  PHP_EOL. "<br>";
            }
        }

    } catch (ApiException $e) {
        // Print an error message.
        print $e->getMessage();
    }
} elseif (isset($_GET['code']) && $_GET['code']) {
    // If an OAuth2 authorization code is present in the URL, exchange it for
    // an access token.
    $oauth->setCode($_GET['code']);
    $oauth->fetchAuthToken();

    // Persist the acquired access token in a session.
    $_SESSION['access_token'] = $oauth->getAccessToken();

    // Persist the acquired refresh token in a session.
    $_SESSION['refresh_token'] = $oauth->getRefreshToken();

    // Refresh the current page.
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/index.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
} else {
    // Redirect to Google's OAuth 2.0 server.
    $auth_url = $oauth->buildFullAuthorizationUri();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
}
// [END analyticsdata_quickstart_oauth2]


