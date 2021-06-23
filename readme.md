# OpenAPI Library

## Installation

```sh
composer require altravia/openapi
```

## Usage

### Instanza della classe

```php
require_once 'vendor/autoload.php';

$openapi = new \OpenApi\OpenApi($scopes, $user, $apikey, $environment);
```

Dove `$scopes` è un array di stringhe o di oggetti in uno dei seguenti formati:

```php
$scopes = [
    "GET:ws.ufficiopostale.com/comuni",
    [
        "domain"=>"ws.ufficiopostale.com", 
        "method"=>"comuni",
        "mode"  =>"GET"
    ]
];
```

...e `$environment` è l'ambiente sceltro tra `'test'` (default) e `'production'`

OpenApi si occuperá di reperire automaticamente, o generare, un nuovo token quando necessario.

A questo punto, in base agli scopes indicati vengono creati i seguenti oggetti:


```php
// Ogni oggetto verrá creato solo se disponibile nello scope.
$openapi->ufficiopostale;
$openapi->comuni;
$openapi->imprese;
$openapi->visengine;
$openapi->marcheTemporali;
$openapi->geocoding;
$openapi->SMS;
$openapi->firmaDigitale;
$openapi->pecMassiva;
```
che possono essere usati al seguente modo:

```php
$this->openapi->ufficioposale->getCitiesByCap('00132');
```
### Esempi 

```php
require_once 'vendor/autoload.php';

// Dichiaro gli scopes necessari
$scopes = [
    'GET:comuni.openapi.it/cap',
    'GET:imprese.altravia.com/advance',
];

$openapi = new OpenApi\OpenApi($scopes, 'my_username','my_api_key', 'test');

// Comuni: prendi informazioni sul cap 00132
$cap = $openapi->comuni->getCitiesByCap('00132');

// Imprese: prendi informazioni su una specifica impresa
$impresa = $openapi->imprese->getByPartitaIva('12485671007');

// Ufficio Postale: ottieni informaizoni sul tracking
$track = $this->openapi->ufficiopostale->track('123456789'); 
```

<!-- ## Modulo ufficio postale
### Creare raccomandata -->

## Modulo comuni
Consente di prendere informazioni su comuni e provincie.

* `getCitiesByCap`
* `getComuneByCatasto`
* `getRegioni`
* `getProvince`
* `getComuni`

### Esempi

```php
$provincia = 'RM';
$comuni = $this->openapi->comuni->getComuni($provincia);

var_dump($comuni['comuni']); 
/*

["nome_provincia"]=>
  string(4) "Roma"
  ["sigla_provincia"]=>
  string(2) "RM"
  ["regione"]=>
  string(5) "Lazio"
  ["comuni"]=>
  array(121) {
    [0]=>
    string(6) "Affile"
    ...
*/


```

<!-- ## Modulo visure
### Utilizzo
Il modulo espone i seguenti metodi: 
* `sendRequest`
* `getRequestByIdVisura`
* `getRequestByData`
* `getDocument`
* `setRicerca`

### `sendRequest($VisRequest)` -->


## Modulo imprese
### Utilizzo
Il modulo imprese espone i seguenti metodi:
* `getByPartitaIva`
* `getClosed`
* `getVatGroup`
* `getPec`
* `getBySearch`

Per `getBySearch` e `getByPartitaIva` è richiesto accesso allo scope `/advance`

### Esempi
Utilizziamo `getBySearch` per cercare un'azienda il cui nome inizia con  `Altrav` a Roma

```php
$autocomplete = $this->openapi->imprese->getBySearch('Altrav*', 'RM');

/*
 [0]=>
  object(stdClass)#41 (10) {
    ["piva"]=>
    string(11) "12485671007"
    ["cf"]=>
    string(11) "12485671007"
    ["denominazione"]=>
    string(20) "ALTRAVIA SERVIZI SRL"
 [1]=>
  object(stdClass)#42 (10) {
    ["id"]=>
    string(24) "4242424242"
    ["denominazione"]=>
    string(18) "xxx Altravia Esempio 2"
    ...
 */
```

## Modulo Marche Temporali
* `availability`
* `checkLotto`
* `purcahse`

### Esempi

```php
// Controlliamo la disponibilitá di una marca di inforcert o aruba
$disponibilita = $this->openapi->marcheTemporali->availability('infocert', 1);

// Se le marche sono disponibili, acquistiamone una
if ($disponibilita->availability > 0) {
    try {
        $marca = $this->openapi->marcheTemporali->purcahse('infocert', 1);
    } catch (\OpenApi\classes\exception\OpenApiMarcheTemporaliException $e) {
        error_log(var_dump($e));
    }
}
```

## Modulo SMS
* `getRecipients`
* `getMessage`
* `sendMore`
* `sendOne`

### Inviare un SMS
Per inviare un SMS, per prima cosa definiamo i destinatari:

```php
$recipient = '+39-3939989741';
// OR
$recipients = [
    [
        'number' => '+39-3939989741', 
        'fields' => ['nome' => 'NomeDestinatario']
    ]
];
```

Possiamo ora procedere ad inviare un SMS:
```php

try {
    $priority = 1;
    $options = null;
    $singleSms = $this->openapi->SMS->sendOne('Nome del mittente', $recipient, 'lorem ipsum', null, $priority, $options);
} catch (\OpenApi\classes\exception\OpenApiConnectionsException $e) {
    throw 'Non è stato possibile recapitare il messaggio';
}
```

Possiamo anche speficiare i prefissi in modo indipendente:
```php
$this->openapi->SMS->sendOne('Nome del mittente', '3939989741', 'lorem ipsum', '+42', 1, null);
```

O passare delle opzioni
```php
$options = ['timestamp_send' => '2021-04-20']
$this->openapi->SMS->sendOne('Nome del mittente', '3939989741', 'lorem ipsum', '+42', 1, $options);
```