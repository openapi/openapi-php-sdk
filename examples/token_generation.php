<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\OauthClient;

try {
    $oauthClient = new OauthClient('<your_username>', '<your_apikey>', true);

    $scopes = [
        'GET:test.imprese.openapi.it/advance',
        'POST:test.postontarget.com/fields/country'
    ];
    
    $ttl = 3600;
    $result = $oauthClient->createToken($scopes, $ttl);

    $tokenData = json_decode($result, true);
    
    if (isset($tokenData['token'])) {
        echo "Generated token: " . $tokenData['token'] . PHP_EOL;
        echo "Token created successfully!" . PHP_EOL;
    } else {
        echo "Error creating token: " . $result . PHP_EOL;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}