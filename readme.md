# OpenAPI Library

## Usage

### Instanza della classe

```
 $this->openapi = new \OpenApi\OpenApi($scopes,$user,$apikey,"test");
```

Dove ```$scopes``` è un array di stringhe o di oggetti in uno dei seguenti formati:

```php
$scopes=[
"GET:ws.ufficiopostale.com/comuni",
["domain"=>"ws.ufficiopostale.com", "method"=>"comuni","mode"=>"GET"]
];
```

A questo punto, in base agli scopes indicati vengono creati i seguenti oggetto:

```php
$this->openapi->ufficiopostale
$this->openapi->imprese
...
```

che possono essere usati al seguente modo:

```php
$this->openapi->ufficioposale->getCitiesByCap('00132');
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
