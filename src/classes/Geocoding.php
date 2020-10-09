<?php
namespace OpenApi\classes;
class Geocoding extends OpenApiBase {

  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://geocoding.realgest.it";
  }

  /**
   * Restituisce le coordinate geografiche a partire dall'indirizzo
   * @param string $address Indirizzo
   * @param int $ttl Tempo  in cui la risposta resta in cahce
   * 
   * @return object
   */
  function geocode(string $address, $ttl = 86400){
    $data = $this->connect("geocode", "POST", ["address" => $address], $ttl, TRUE);

    return $data;
  }

}