<?php
namespace OpenApi\classes;
class Uploader extends OpenApiBase {

  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://uploader.altravia.com";
  }

  function newCollection(){
    
    return new \OpenApi\classes\utility\Uploader\Collection([$this, 'connect']);
  }

  function gateway(){
    
    $endpoint  = isset($_GET['endpoint'])?$_GET['endpoint']:null;
    if($endpoint == NULL){
      throw new \OpenApi\classes\exception\OpenApiUploaderException("No endpoint GET",40018);
    }
    //echo $endpoint;exit;
    
    $method = $_SERVER['REQUEST_METHOD'];
    $data = null;
    if($method != "GET" && $method != "DELETE"){
      $data =  file_get_contents("php://input");
      $data = json_decode($data);
    }
    try{
      $data = $this->connect($endpoint, $method,$data);
    }catch(\OpenApi\classes\exception\OpenApiConnectionsException $e){

      $message = $e->getMessage();
      var_dump($message);
      //var_dump($e->getServerRawResponse());
      $message = explode("name (must be one of",$message);
      $valid_ext = "";
      if(isset($message[1])){
        $message = substr($message[1],0,-1);
        
        $message = explode(",",$message);
        foreach($message as $m){
          $m = trim($m);
          $m = explode("/",$m);
          if(isset($m[1])){
            $m = $m[1];
            if($m == "jpeg"){
              $vext[] = "jpg";
            }
            $vext[] = $m;
          }
          
          
        }

        $valid_ext = ", è possibile caricare esclusivamente file con formato: ".implode(", ",$vext);
        
      }
      $ret['success'] = FALSE;
      $ret['message'] = "C'è stato un errore in fase di caricamento{$valid_ext}";
      $ret['error'] = $e->getCode();
      echo json_encode($ret);
      exit;
    }catch(\Exception $e){
      $ret['success'] = FALSE;
      $ret['message'] = "C'è stato un errore in fase di caricamento inaspettato, riprovare, se il problema persiste contattare la nostra assistenza";
      echo json_encode($ret);
      exit;  
    }
    
    header("Content-Type: ",$this->parsedHEader['Content-Type']);
    if(isset($this->parsedHEader['Content-Type']) && strtolower($this->parsedHEader['Content-Type']) == "application/json") {
      echo json_encode($data);
    }else{
      echo $data;
    }
  }

  function createCollection($outputFormat, $outputSize = null, $inputTypes = null, $inputCount = null, $public = null, $watermark = null, $watermarkPosition = NULL, $expireTimestamp = null) {
    $collection = new \OpenApi\classes\utility\Uploader\Collection([$this, 'connect']);
    $collection->setOutput($outputFormat);
    $collection->setOutputSize($outputSize);
    $collection->setInputTypes($inputTypes);
    $collection->setInputCount($inputCount);
    $collection->setPublic($public);
    $collection->setWaterMark($watermark);
    $collection->setWaterMarkPosition($watermarkPosition);
    $collection->setExpireTimestamp($expireTimestamp);
    $collection->save();
  }

  function getCollectionById($id){
    $coll = $this->connect("collections/$id","GET");
    $collection = new \OpenApi\classes\utility\Uploader\Collection([$this, 'connect']);
    if(isset($coll->data)){
      $collection->parseData($coll->data);
      return $collection;
    }
    return null;
  }

  function deleteCollectionById($id){
    return $this->connect("collections/$id","DELETE");
  }

  function getCollections(){
    $collections = $this->connect("collections","GET");
    foreach($collections->data as $d){
      $d->id =$d->_id->{'$oid'};
      unset($d->_id);
    }
    return $collections;
  }

}