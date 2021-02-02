<?php
namespace OpenApi\classes;
class UfficioPostale extends OpenApiBase {

  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://ws.ufficiopostale.com";
  }

   
  /**
   * Restiuisce un oggetto di tipo raccomandata
   * @return object
   */
  function createRaccomandata(){
    return new \OpenApi\classes\utility\UfficioPostale\Raccomandata(function(string $endpoint, $type = "GET", $param = [], $ttr = 0, $force = false, $forceRaw = false){

      return $this->connect( $endpoint, $type, $param , $ttr , $force, $forceRaw);
    });
  }

  //function createRaccomandataByData()

  
  

  
}