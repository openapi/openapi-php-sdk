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
    
    $method = $_SERVER['REQUEST_METHOD'];
    $data = null;
    if($method != "GET" && $method != "DELETE"){
      $data =  file_get_contents("php://input");
      $data = json_decode($data);
    }
    $data = $this->connect($endpoint, $method,$data);
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