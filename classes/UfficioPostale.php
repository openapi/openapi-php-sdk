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
   * 
   * A partire dal CAP restistuisce un'array di oggietti di tipo comune 
   * 
   * @param string $cap Il cap da ricercare
   * @param int $ttl Il tempo di chache degli oggetti ritornati, 0 per no chche
   * 
   * @return array
   */
  function getCitiesByCap(string $cap, $ttl = 86400){
    $data = $this->connect("comuni/$cap", "GET", [], $ttl);
    return $data->data;
  }
  
}