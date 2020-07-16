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
 $this->openapi->ufficioposale
 $this->openapi->imprese
 ```
 che possono essere usati al seguente modo:
 ```php
 $this->openapi->ufficioposale->getCitiesByCap('00132';)
 ```