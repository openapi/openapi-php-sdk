<?php
namespace OpenApi\classes;
class Comuni extends OpenApiBase {

  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://comuni.openapi.it";
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
    $data = $this->connect("cap/$cap", "GET", [], $ttl);
    return $data->data;
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
  function getComuneByCatasto(string $codice_catastale, $ttl = 86400){
    $data = $this->connect("catastale/$codice_catastale", "GET", [], $ttl);
    return $data->data;
  }

  /**
   * Restituisce la lista delle regioni italiani
   * 
   * @return array
   */
  function getRegioni($ttl = 86400){
    $data = $this->connect("regioni", "GET", [], $ttl);
    $regioni = $data->data;
    sort($regioni);
    return $regioni;
  }
  
  /**
   * Restituisce la lsita delle province italiane (a partire dalla regione)
   * @param string $regione La regione per la quale recuperare le regioni
   * 
   * @return array
   */
  function getProvince($regione = NULL, $ttl = 86400){
    if($regione == NULL){
      $data = $this->connect("province", "GET", [], $ttl);
      $province = $data->data;
      
      $_province = [];
      foreach($province as $key => $p){
        $provincia = new \stdClass();
        $provincia->nome_provincia = $p;
        $provincia->sigla_provincia = $key;
        $_province[] = $provincia;
      }
      
      usort($_province,[$this, 'sortProcince']);
      return $_province;
    }
    $regione = explode("/",$regione)[0];
    $regione = trim(\strtolower($regione));
    $regione = urlencode($regione);

    $data = $this->connect("regioni/$regione", "GET", [], $ttl);
    
  
    $province = $data->data;
    usort($province,[$this, 'sortProcince']);
    return $province;
  }

  
  /**
   * Restituisce la lista comuni a partire dalla provincia
   * @param mixed $provincia provincia Es.: RM
   * @param int $ttl time to reload cache
   * 
   * @return array
   */
  function getComuni($provincia, $ttl = 86400){
    
    $provincia = trim(\strtolower($provincia));
    $data = $this->connect("province/$provincia", "GET", [], $ttl);
    
    $comuni = $data->data;
    return $comuni;
 
  }

  private function sortComune($a, $b){
    if($a->nome == $b->nome){
      return 0;
    }
    return $a->nome < $b->nome ? -1 : 1;
  }

  private function sortProcince($a, $b){
    if($a->nome_provincia == $b->nome_provincia){
      return 0;
    }
    return $a->nome_provincia < $b->nome_provincia ? -1 : 1;
  }

}