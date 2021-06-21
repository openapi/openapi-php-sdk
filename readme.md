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

## Modulo ufficio postale
### Creare raccomandata


## Modulo visure
### Utilizzo
Il modulo espone i seguenti metodi: 
* `sendRequest`
* `getRequestByIdVisura`
* `getRequestByData`
* `getDocument`
* `setRicerca`

### `sendRequest($VisRequest)`


## Modulo imprese
### Utilizzo
Il modulo imprese espone i seguenti metodi:
* `getByPartitaIva`
* `getClosed`
* `getVatGroup`
* `getPec`
* `getBySearch`

Per `getBySearch` e `getByPartitaIva` è richiesto accesso allo scope `/advance`

## Modulo SMS
