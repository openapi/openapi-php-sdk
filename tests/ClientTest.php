<?php

use OpenApi\classes\utility\UfficioPostale\Objects\Recipient;
use OpenApi\classes\utility\UfficioPostale\Objects\Sender;
use OpenApi\classes\utility\VisEngine\VisRequest;
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
            "GET:ws.ufficiopostale.com/raccomandate",
            "GET:imprese.altravia.com/autocomplete",
            "GET:imprese.altravia.com/base",
            "GET:imprese.altravia.com/advance",
            "GET:imprese.altravia.com/pec",
            "GET:imprese.altravia.com/autocomplete",
            "GET:imprese.altravia.com/closed",
            "GET:imprese.altravia.com/gruppoiva",
            "GET:comuni.openapi.it/cap",
            "GET:comuni.openapi.it/istat",
            "GET:comuni.openapi.it/province",
            "GET:comuni.openapi.it/regioni",
            "GET:comuni.openapi.it/catastale",
            "GET:ws.ufficiopostale.com/tracking",
            "POST:geocoding.realgest.it/geocode",
            "POST:ws.messaggisms.com/messages",
            "GET:ws.messaggisms.com/messages",
            "PUT:ws.messaggisms.com/messages",
            "GET:ws.firmadigitale.com/richiesta",
            "POST:ws.firmadigitale.com/richiesta",
            "GET:ws.marchetemporali.com/availability",
            "GET:ws.marchetemporali.com/marche",
            "POST:ws.marchetemporali.com/check_lotto",
            "POST:ws.marchetemporali.com/marca",
            "POST:ws.marchetemporali.com/verifica",
            "POST:ws.marchetemporali.com/analisi",
        ];

        $this->openapi = new OpenApi($this->scopes, $this->username, $this->api_key, 'test');
    }


    public function testClientInstance() {
        $this->assertInstanceOf('OpenApi\OpenApi', $this->openapi);
    }

    // public function testComuni() {
    //     // Prendi informazioni sul cap 00132
    //     $cap = $this->openapi->comuni->getCitiesByCap('00132');
    //     $comune = $this->openapi->comuni->getComuneByCatasto('117');
    //     $comuni = $this->openapi->comuni->getComuni('RM');
    //     $regioni = $this->openapi->comuni->getRegioni();
    //     $provincie = $this->openapi->comuni->getProvince();
        
    //     $this->assertIsArray($cap);
    //     $this->assertIsArray($comune);
    //     $this->assertIsArray($comuni);
    //     $this->assertIsArray($regioni);
    //     $this->assertIsArray($provincie);

    //     var_dump($comuni[0]->nome);
    // }

    // public function testImprese() {
    //     $impresa = $this->openapi->imprese->getByPartitaIva('00966950230');
    //     $autocomplete = $this->openapi->imprese->getBySearch('Altrav*', 'RM');
    //     $closed = $this->openapi->imprese->getClosed('00966950230');
    //     $vat = $this->openapi->imprese->getVatGroup('00966950230');
    //     $Pec = $this->openapi->imprese->getPec('00966950230');

    //     $this->assertEquals($impresa->provincia, 'RM');
    //     $this->assertIsArray($autocomplete);
    //     var_dump($autocomplete);
    //     $this->assertIsBool($closed->cessata);
    //     $this->assertIsObject($vat);
    //     $this->assertIsObject($Pec);
    // }

    // public function testMarche() {
    //     $marca = $this->openapi->marcheTemporali->availability('infocert', 1);
    //     $comprata = $this->openapi->marcheTemporali->purcahse('infocert', 1);

    //     $this->assertIsInt($marca->availability);
    // }

    // public function testGeocoding() {
    //     // Prendi informazioni sul cap 00132
    //     $cap = $this->openapi->geocoding->geocode('Via Cristoforo Colombo, Roma RM');
    //     $this->assertIsArray($cap);
    // }

    // public function testUfficioPostale() {
    //     $track = $this->openapi->ufficiopostale->track($_ENV['TRACK_TEST']);
    //     $this->assertEquals(true, $track->success);
    //     var_dump($track);

    //     $raccomandata = $this->openapi->ufficiopostale->createRaccomandata();
    //     var_dump($raccomandata);

    //     $data = new stdClass();
    //     $sender = new Sender([
    //         'firstName' => 'John',
    //         'secondName' => 'Doe',
    //         'companyName' => 'example-spa',
    //     ]);

    //     $recipient = new Recipient([
    //         'firstName' => 'John',
    //         'secondName' => 'Doe',
    //         'companyName' => 'example-spa',
    //     ]);

    //     $data->sender = $sender;
    //     $data->recipient = $recipient;
    
    //     $raccomandata->creaRaccomandataByData($data);
    // }

    public function testSms() {
        $recipients = [
            [
                'number' => '+39-3939989741', 
                'fields' => ['nome' => 'NomeDestinatario']
            ]
        ];
        $singleSms = $this->openapi->SMS->sendOne('test', '+39-3939989741', 'prova', null, 1, null, true);

        $message = $this->openapi->SMS->getMessage($singleSms->data->id);
        
        $this->assertEquals(true, $singleSms->success);
        $this->assertEquals(true, $message['success']);
    }

    // public function testVisura() {
    //     // $visura = new VisRequest('eccbc87e4b5ce2fe28308fd9f2a7baf3');
    //     $response = $this->openapi->visengine->getRequestByIdVisura('eccbc87e4b5ce2fe28308fd9f2a7baf3');
    //     $this->assertNotEmpty($response);
    //     var_dump($response);
    // }

    // public function testFirmaDigitale() { 
    //     $data = json_decode(file_get_contents(__DIR__.'/esempio_firma.json'), true);
    //     $data['codice_prodotto'] = 'FIR';
    //     $response = $this->openapi->firmaDigitale->requestProduct($data);
    //     $this->assertNotEmpty($response);
    // }
}