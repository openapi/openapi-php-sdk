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
    $this->errorClass = NULL;
      $this->setErrorLang('it');
    $this->basePath = "https://ws.ufficiopostale.com";
  }


  /**
     * Prende in ingresso la path di un json contenente le traduzioni degli errori
     */
    function setErrorLang($errorLang){
      $this->errorClass = new \OpenApi\classes\utility\UfficioPostale\Objects\ErrorTranslation\errorLang($errorLang);
    }
   
    function getComuniByCAP($cap){
      $data = $this->connect("comuni/$cap", "GET");
      return $data->data;
      
    }

    function create( $prodotto){
      if($prodotto == "raccomandata"){
        return $this->createRaccomandata();
      }
      if($prodotto == "telegramma"){
        return $this->createTelegramma();
      }
      if($prodotto == "posta-prioritaria"){
        return $this->createPostaPrioritaria();
      }
      if($prodotto == "posta-ordinaria"){
        return $this->createPostaOrdinaria();
      }
      $ex = new \OpenApi\classes\exception\OpenApiUPException("Il prodotto $prodotto non esiste",40014);
      throw $ex;
      
    }
  /**
   * Restiuisce un oggetto di tipo raccomandata
   * @return object
   */
  function createRaccomandata(){
    return new \OpenApi\classes\utility\UfficioPostale\Raccomandata(function(string $endpoint, $type = "GET", $param = [], $ttr = 0, $force = false, $forceRaw = false){

      return $this->connect( $endpoint, $type, $param , $ttr , $force, $forceRaw);
    },

    function($code, $serverResponse){
      
      return $this->errorClass->getError( $code, $serverResponse);
    });
  }

  function createPostaPrioritaria(){
    return new \OpenApi\classes\utility\UfficioPostale\PostaPrioritaria(function(string $endpoint, $type = "GET", $param = [], $ttr = 0, $force = false, $forceRaw = false){

      return $this->connect( $endpoint, $type, $param , $ttr , $force, $forceRaw);
    },

    function($code, $serverResponse){
      
      return $this->errorClass->getError( $code, $serverResponse);
    });
  }

  function createPostaOrdinaria(){
    return new \OpenApi\classes\utility\UfficioPostale\PostaOrdinaria(function(string $endpoint, $type = "GET", $param = [], $ttr = 0, $force = false, $forceRaw = false){

      return $this->connect( $endpoint, $type, $param , $ttr , $force, $forceRaw);
    },

    function($code, $serverResponse){
      
      return $this->errorClass->getError( $code, $serverResponse);
    });
  }

  function createTelegramma(){
    return new \OpenApi\classes\utility\UfficioPostale\Telegramma(function(string $endpoint, $type = "GET", $param = [], $ttr = 0, $force = false, $forceRaw = false){

      return $this->connect( $endpoint, $type, $param , $ttr , $force, $forceRaw);
    },

    function($code, $serverResponse){
      
      return $this->errorClass->getError( $code, $serverResponse);
    });
  }


  function track($tracking_code){
    return $this->connect("tracking/$tracking_code", "GET");
  }

  function getById($id, $prodotto){
    if($prodotto == "raccomandata"){
      return $this->getRaccomandataById($id);
    }
    if($prodotto == "telegramma"){
      return $this->getTelegrammaById($id);
    }
    if($prodotto == "posta-prioritaria"){
      return $this->getPostaPrioritariaById($id);
    }
    if($prodotto == "posta-ordinaria"){
      return $this->getPostaOrdinariaById($id);
    }
    $ex = new \OpenApi\classes\exception\OpenApiUPException("Il prodotto $prodotto non esiste",40014);
    throw $ex;
    
  }

  function getByData($data, $prodotto){
    if($prodotto == "raccomandata"){
      return $this->getRaccomandataByData($data);
    }
    if($prodotto == "telegramma"){
      return $this->getTelegrammaByData($data);
    }
    if($prodotto == "posta-prioritaria"){
      return $this->getTelegrammaByData($data);
    }
    if($prodotto == "posta-ordinaria"){
      return $this->getPostaOrdinariaByData($data);
    }
    $ex = new \OpenApi\classes\exception\OpenApiUPException("Il prodotto $prodotto non esiste",40014);
    throw $ex;
  }

  function getRaccomandataById($id){
    $data = $this->connect("raccomandate/$id", "GET");
    return $this->getRaccomandataByData($data->data);
  }

  function getRaccomandataByData($data){
    $busta = $this->createRaccomandata();
    $busta->creaRaccomandataByData($data);
    return $busta;
  }

  function getPostaPrioritariaById($id){
    $data = $this->connect("prioritarie/$id", "GET");
    return $this->getPostaPrioritariaByData($data->data);
  }

  function getPostaPrioritariaByData($data){
    $busta = $this->createPostaPrioritaria();
    $busta->creaPostaPrioritariaByData($data);
    return $busta;
  }

  function getPostaOrdinariaById($id){
    $data = $this->connect("ordinarie/$id", "GET");
    return $this->getPostaOrdinariaByData($data->data);
  }

  function getPostaOrdinariaByData($data){
    $busta = $this->createPostaOrdinaria();
    $busta->creaPostaOrdinariaByData($data);
    return $busta;
  }


  function getTelegrammaById($id){
    $data = $this->connect("telegrammi/$id", "GET");
    
    return $this->getTelegrammaByData($data->data);
  }

  function getTelegrammaByData($data){
    $busta = $this->createTelegramma();
    $busta->creaTelegrammaByData($data);
    return $busta;
  }
  function getPricing($type = NULL){
    return $this->connect("pricing/$type", "GET");
  }


  function getDug(){
    return $this->connect("dug/", "GET");
  }

  
  

  
}