<?php

use OpenApi\classes\utility\UfficioPostale\Objects\Recipient;
use OpenApi\classes\utility\UfficioPostale\Objects\Sender;
use OpenApi\OpenApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

require_once 'vendor/autoload.php';

final class ClientTest extends TestCase {

    public Dotenv $dotenv;
    public string $username;
    public string $api_key;
    public array $scopes;
    public OpenApi $openapi;

    public function __construct() {
        parent::__construct();
        $this->dotenv = new Dotenv();
        $this->dotenv->load(__DIR__.'/../.env');

        $this->username = $_ENV['OPENAPI_USERNAME'];
        $this->api_key = $_ENV['API_KEY'];
        
        // Dichiaro gli scopes necessari
        $this->scopes = [
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
            "GET:ws.ufficiopostale.com/tracking",
            "POST:geocoding.realgest.it/geocode"
        ];

        $this->openapi = new OpenApi($this->scopes, $this->username, $this->api_key, 'test');
    }


    public function testClientInstance() {
        $this->assertInstanceOf('OpenApi\OpenApi', $this->openapi);
    }

    public function testComuni() {
        // Prendi informazioni sul cap 00132
        $cap = $this->openapi->comuni->getCitiesByCap('00132');
        $this->assertIsArray($cap);
    }

    // public function testGeocoding() {
    //     // Prendi informazioni sul cap 00132
    //     $cap = $this->openapi->geocoding->geocode('Via Cristoforo Colombo, Roma RM');
    //     $this->assertIsArray($cap);
    // }

    public function testUfficioPostale() {
        $track = $this->openapi->ufficiopostale->track($_ENV['TRACK_TEST']);
        $this->assertEquals(true, $track->success);
        var_dump($track);

        $raccomandata = $this->openapi->ufficiopostale->createRaccomandata();
        var_dump($raccomandata);

        $data = new stdClass();
        $sender = new Sender([
            'firstName' => 'John',
            'secondName' => 'Doe',
            'companyName' => 'example-spa',
        ]);

        $recipient = new Recipient([
            'firstName' => 'John',
            'secondName' => 'Doe',
            'companyName' => 'example-spa',
        ]);

        $data->sender = $sender;
        $data->recipient = $recipient;
    
        $raccomandata->creaRaccomandataByData();
    }
}