<?php
namespace OpenApi\classes;
class Pec extends OpenApiBase {

  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://pec.openapi.it";
  }

  function verify($pec){
    try{
      $data = $this->connect("verifica_pec/$pec", "GET");
      return $data;
    }catch(\OpenApi\classes\exception\OpenApiConnectionsException $e){
      $res = $e->getServerResponse();
      var_dump($res);
      if(isset($res->message) && strpos($res->message,"not available") !== false){
        return ["success"=>true,"data"=>["available" => false]];
      }
      
      return $res;
    }
    
  }

}