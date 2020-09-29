<?php
namespace OpenApi\classes;
class MarcheTemporali extends OpenApiBase {

  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://ws.marchetemporali.com";
  }

  function availability(string $type, int $qty){
    $data = $this->connect("availability/$type/$qty", "GET", []);
    return $data->data;
  }

  function checkLotto($username, $password){

    if(substr($username,0,4) == "FAKE" && substr($password,0,4) == "FAKE"){
      $ret = new \stdClass();
      $ret->data = new \stdClass();
      $ret->data->available = 10;
      $ret->data->used = 90;
      $ret->message = "DESCR= Marche per $username; disponibili 10 consumate 0";
      $ret->success = TRUE;
      $ret->error = NULL;
      return $ret->data;
    }
    $data = $this->connect("check_lotto", "POST", ["username"=>$username, "password"=> $password]);
    
    return $data->data;
  }

  function purcahse(string $type, int $qty){
    $data = $this->availability($type, $qty);
    //var_dump($data);exit;
    if($data->availability == 0){
      throw new \OpenApi\classes\exception\OpenApiMarcheTemporaliException("$qty $type time stamps is not availabile for purchase",40009);
    }
    //echo "marche/$type/$qty";exit;
    $data = $this->connect("marche/$type/$qty", "GET", []);
    //var_dump($data);exit;
    return $data->data;
  }
  

  
}