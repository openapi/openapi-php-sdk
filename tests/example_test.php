<?php

require_once 'vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__.'/../.env');

$username = $_ENV['OPENAPI_USERNAME'];
$api_key = $_ENV['API_KEY'];

// Dichiaro gli scopes necessari
$scopes = [
    "GET:ws.ufficiopostale.com/telegrammi",
    "GET:imprese.altravia.com/autocomplete",
    "GET:imprese.openapi.it/base",
    "GET:imprese.openapi.it/advance",
    "GET:imprese.openapi.it/pec",
    "GET:imprese.openapi.it/autocomplete",
    "GET:imprese.openapi.it/closed",
    "GET:imprese.openapi.it/gruppoiva",
    "GET:comuni.openapi.it/cap",
    "GET:comuni.openapi.it/istat",
    "GET:comuni.openapi.it/regioni",
    "GET:comuni.openapi.it/province",
];

$openapi = new OpenApi\OpenApi($scopes, $username, $api_key, 'test');

// Prendi informazioni sul cap 00132
$cap = $openapi->comuni->getCitiesByCap('00132');
var_dump($cap);

// Prendi informazioni su una specifica impresa
$impresa = $openapi->imprese->getByPartitaIva('12485671007');
var_dump($impresa);

$cerca_impresa = $openapi->imprese->getBySearch('Altravia', 'RM');
var_dump($cerca_impresa);
