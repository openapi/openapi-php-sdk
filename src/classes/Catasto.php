<?php
namespace OpenApi\classes;
class Catasto extends OpenApiBase {

  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://catasto.openapi.it";
  }


  function ufficiCatastali($ttl = 86400){
    $data = $this->connect("territorio", "GET", [], $ttl);
    return $data->data;
  } 

  function getComuni($provincia, $ttl = 86400){
    $data = $this->connect("territorio/$provincia", "GET", [], $ttl);
    return $data->data; 
  }

  function elencoImmobili($tipo_catasto, $provincia,$comune,$sezione, $foglio,$particella, $callback){
    $param = [
      "tipo_catasto" => $tipo_catasto,
      "provincia" => trim($provincia),
      "comune" => $comune,
      "sezione" => $sezione,
      "foglio" => $foglio,
      "particella" => $particella,
      
    ];
    if($callback != NULL){
      $param['callback'] = $callback;
    }
    $data = $this->connect("richiesta/elenco_immobili", "POST", $param);
    return $data->data;
  }

  function getComune($provincia, $comune, $ttl = 86400){
    $comune = urlencode($comune);
    $data = $this->connect("territorio/$provincia/$comune", "GET", [], $ttl);
    return $data->data;
  }
  function prospettoCatastale($tipo_catasto, $provincia,$comune,$sezione, $foglio,$particella,$subalterno, $callback=NULL){
    $param = [
      "tipo_catasto" => $tipo_catasto,
      "provincia" => trim($provincia),
      "comune" => $comune,
      "sezione" => $sezione,
      "foglio" => $foglio,
      "particella" => $particella,
      "subalterno" => $subalterno,
    ];
    if($callback != NULL){
      $param['callback'] = $callback;
    }
    $data = $this->connect("richiesta/prospetto_catastale", "POST", $param);
    return $data->data;
  }

  function ricercaPersona($tipo_catasto,$cf_piva,$provincia,$callback = NULL){
    $param = [
      "tipo_catasto" => $tipo_catasto,
      "cf_piva" => trim($cf_piva),
      "provincia" => $provincia,
    ];
    //var_dump($param);exit;
    if($callback != NULL){
      $param['callback'] = $callback;
    }
    $data = $this->connect("richiesta/ricerca_persona", "POST", $param);
    return $data->data;
  }

  function ricercaNazionale($tipo_catasto,$cf_piva,$callback= NULL){
    $param = [
      "tipo_catasto" => $tipo_catasto,
      "cf_piva" => trim($cf_piva)
    ];
    //var_dump($param);exit;
    if($callback != NULL){
      $param['callback'] = $callback;
    }
    $data = $this->connect("richiesta/ricerca_nazionale", "POST", $param);
    return $data->data;
  }

  


}