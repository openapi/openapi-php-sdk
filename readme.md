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
$openapi->ufficiopostale
$openapi->comuni
$openapi->imprese
$openapi->visengine
$openapi->marcheTemporali
$openapi->geocoding
$openapi->SMS
$openapi->firmaDigitale
$openapi->pecMassiva
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

$openapi = new OpenApi\OpenApi($scopes, 'my_username','my_api_key', 'production');

// Prendi informazioni sul cap 00132
$cap = $openapi->comuni->getCitiesByCap('00132');

// Prendi informazioni su una specifica impresa
$impresa = $openapi->imprese->getByPartitaIva('12485671007');
```

# Modulo ufficio postale

# Modulo visure

# Modulo imprese

## `getByPartitaIva`

### Introduction

La funzione consente di recuperare i dati aziendali a partire dalla partita IVA

### Description

`function getByPartitaIva(string $partitaIva, $ttl = 86400):object`

* $partitaIva: La partita IVA da cercare
* $ttl: Time To Release, per quanti secondi la chiamata resta in cache prima di essere effettuata una seconda volta

