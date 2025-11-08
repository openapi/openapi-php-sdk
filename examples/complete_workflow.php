<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\OauthClient;
use OpenApi\Client;
use OpenApi\Exception;

try {
    echo "=== OpenAPI PHP SDK Complete Workflow Example ===" . PHP_EOL . PHP_EOL;

    // Step 1: Create OAuth client
    echo "Step 1: Creating OAuth client..." . PHP_EOL;
    $oauthClient = new OauthClient('<your_username>', '<your_apikey>', true);
    echo "✓ OAuth client created" . PHP_EOL . PHP_EOL;

    // Step 2: Generate token
    echo "Step 2: Generating access token..." . PHP_EOL;
    $scopes = [
        'GET:test.imprese.openapi.it/advance',
        'POST:test.postontarget.com/fields/country'
    ];

    $tokenResult = $oauthClient->createToken($scopes, 3600);
    $tokenData = json_decode($tokenResult, true);

    if (!isset($tokenData['token'])) {
        throw new Exception('Failed to generate token: ' . $tokenResult);
    }

    $token = $tokenData['token'];
    echo "✓ Token generated: " . substr($token, 0, 20) . "..." . PHP_EOL . PHP_EOL;

    // Step 3: Create API client
    echo "Step 3: Creating API client..." . PHP_EOL;
    $apiClient = new Client($token);
    echo "✓ API client created" . PHP_EOL . PHP_EOL;

    // Step 4: Make API calls
    echo "Step 4: Making API calls..." . PHP_EOL;

    // GET request
    echo "  → Making GET request..." . PHP_EOL;
    $getParams = [
        'denominazione' => 'altravia',
        'provincia' => 'RM',
        'codice_ateco' => '6201'
    ];
    $getResponse = $apiClient->get('https://test.imprese.openapi.it/advance', $getParams);
    echo "  ✓ GET response received (" . strlen($getResponse) . " bytes)" . PHP_EOL;

    // POST request  
    echo "  → Making POST request..." . PHP_EOL;
    $postPayload = [
        'limit' => 10,
        'query' => [
            'country_code' => 'IT'
        ]
    ];
    $postResponse = $apiClient->post('https://test.postontarget.com/fields/country', $postPayload);
    echo "  ✓ POST response received (" . strlen($postResponse) . " bytes)" . PHP_EOL . PHP_EOL;

    echo "=== Workflow completed successfully! ===" . PHP_EOL;

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . PHP_EOL;
    
    if ($e->getHttpCode()) {
        echo "  HTTP Code: " . $e->getHttpCode() . PHP_EOL;
    }
    
    if ($e->getServerResponse()) {
        echo "  Server Response: " . json_encode($e->getServerResponse()) . PHP_EOL;
    }
}