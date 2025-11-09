<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Client;

try {
    $token = '<your_access_token>';
    $client = new Client($token);

    // GET request with parameters
    $params = [
        'denominazione' => 'altravia',
        'provincia' => 'RM', 
        'codice_ateco' => '6201'
    ];

    $result = $client->get('https://test.imprese.openapi.it/advance', $params);
    echo "GET API Response: " . $result . PHP_EOL;

    // POST request with payload
    $payload = [
        'limit' => 10,
        'query' => [
            'country_code' => 'IT'
        ]
    ];

    $result = $client->post('https://test.postontarget.com/fields/country', $payload);
    echo "POST API Response: " . $result . PHP_EOL;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}