<?php
namespace OpenApi\classes;
class Imprese extends OpenApiBase {
  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    
    //$prefix = "";
    $this->basePath = "https://imprese.altravia.com";
  }

  /**
   * 
   * Consente di recuperare i dati di una azienda a partire dalla partita IVA
   * 
   * @param string $partitaIva La partita IVa da ricercare
   * @param int $ttr Time to Release: per quanti secondi la chiamata resta in cache prima di essere effettuata una seconda volta
   * 
   * @return object
   */
  function getByPartitaIva(string $partitaIva, $ttr = 86400, $force = false){
    $partitaIva = trim($partitaIva);
    try{
      $data = $this->connect("advance/$partitaIva", "GET", [], $ttr, true);
      return $data->data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      
      
      exit;
    }
    
    
  }

  function getClosed(string $partitaIva, $ttr = 86400){
    $partitaIva = trim($partitaIva);
    try{
      $data = $this->connect("closed/$partitaIva", "GET", [], $ttr);
      return $data->data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      
      
      exit;
    }
  }

  function getVatGroup(string $partitaIva, $ttr = 86400){
    $partitaIva = trim($partitaIva);
    try{
      $data = $this->connect("gruppoIva/$partitaIva", "GET", [], $ttr);
      return $data->data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      
      
      exit;
    }
  }

  function getPec(string $partitaIva, $ttr = 86400){
    $partitaIva = trim($partitaIva);
    try{
      $data = $this->connect("pec/$partitaIva", "GET", [], $ttr);
      return $data->data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      
      
      exit;
    }
  }

  /**
   * 
   * Cerca un'azienda o più utilizzando vari parametri
   * 
   * @param string $denominazione       Denominazione azienda
   * @param string $provincia           Provincia
   * @param string $partitaIva=NULL     Partita IVA
   * @param string $codiceFiscale=NULL  Codice Fiscale
   * @param int $ttr                    Time to Release: per quanti secondi la chiamata resta in cache prima di essere effettuata una seconda volta
   * 
   * @return array                      Lista delle aziende individuate
   */
  function getBySearch(string $denominazione, string $provincia,  $partitaIva= NULL ,  $codiceFiscale=NULL, $ttr = 86400){
    $params=[];
    if($denominazione != NULL){
      $params['denominazione'] = trim($denominazione);
    }
    if($provincia != NULL){
      $params['provincia'] = $provincia;
    }
    if($partitaIva != NULL){
      $params['piva'] = $partitaIva;
    }
    if($codiceFiscale != NULL){
      $params['cf'] = $codiceFiscale;
    }
    
    $data = $this->connect("advance/$partitaIva", "GET", $params, $ttr);
    return $data->data;
  }
}