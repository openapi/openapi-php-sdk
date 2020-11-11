<?php
namespace OpenApi\classes\utility\Uploader;
class Collection {
  private $output = null;
  private $id = null;
  private $connect = null;
  private $public = false;
  private $outputSize = null;
  private $outputGroup = false;
  private $inputTypes = false;
  private $inputCount = 0;
  private $watermark = null;
  private $watermarkPosition = null;
  private $expireTimestamp = null;
  private $state = 0;

  private $email = null;
  private $creation_timestamp = null;
  private $update_timestamp = null;
  private $documents_count = null;
  private $documents_bytes = null;
  private $documents = null;
  
  private $validPdfFormats = ["A0","A1","A2","A3","A4","A5","A6","A7","A8","A9","A10","B0","B1","B2","B3","B4","B5","B6","C0","C1","C2","C3","C4","C5","C6","Ledger","Legal","Letter","Half-Letter","Tabloid",
  "Landscape-A0","Landscape-A1","Landscape-A2","Landscape-A3","Landscape-A4","Landscape-A5","Landscape-A6","Landscape-A7","Landscape-A8","Landscape-A9","Landscape-A10","Landscape-B0","Landscape-B1","Landscape-B2","Landscape-B3","Landscape-B4","Landscape-B5","Landscape-B6","Landscape-C0","Landscape-C1","Landscape-C2","Landscape-C3","Landscape-C4","Landscape-C5","Landscape-C6","Landscape-Ledger","Landscape-Legal","Landscape-Letter","Landscape-Half-Letter","Landscape-Tabloid"];
  function __construct($connect)
  { 
      $this->connect = $connect;
  }

  function save($state = false){
    $state = (bool)$state;
    if($this->getOutput() == NULL){
      throw new \OpenApi\classes\exception\OpenApiUploaderException("Output format not setted",40017);
    }
    $this->state = $state;
    $data['output_format'] = $this->getOutput();
    if($this->getOutputSize() != NULL){
      $data['output_size'] = $this->getOutputSize();
    }
    if($this->getInputTypes() != NULL){
      $data['input_types'] = $this->getInputTypes();
    }
    if($this->getInputCount() != NULL){
      $data['input_count'] = $this->getInputCount();
    }
    $data['public'] = $this->isPublic();
    if($this->getWaterMark() != NULL){
      $data['watermark'] = $this->getWaterMark();
      $data['watermark_position'] = $this->getWaterMarkPosition();
    }
    if($this->getExpireTimestamp() != NULL){
      $data['expire_timestamp'] = $this->getExpireTimestamp();
    }
    $data['state'] = $state;
    if($this->id == null){
      
      $ret = call_user_func_array ($this->connect,["collections","POST",$data]);
      $this->id = $ret->id;
      return $ret->id;
    }else{
      
      $ret = call_user_func_array ($this->connect,["collections/$this->id","PATCH",$data]);
      $this->id = $ret->id;
      return $ret->id;
    }
  }

  function parseData($data){
    
    $this->setOutput($data->output_format);
    $this->setOutputSize($data->output_size);
    $this->setInputTypes($data->input_types);
    $this->setInputCount($data->input_count);
    $this->setPublic($data->public);
    $this->setWaterMark($data->watermark);
    $this->setWaterMarkPosition($data->watermark_position);
    $this->setExpireTimestamp($data->expire_timestamp);
    $this->email = $data->email;
    $this->creation_timestamp = $data->creation_timestamp;
    $this->update_timestamp = $data->update_timestamp;
    $this->documents_count = $data->documents_count;
    $this->documents_bytes = $data->documents_bytes;
    $this->documents = $data->documents;
    $this->state = $data->state;
    $this->id = $data->_id->{'$oid'};
    
  }

  function getState(){
    return $this->state;
  }

  function getDocuments(){
    $this->documents;
  }

  function getDocumentBytes(){
    $this->documents_bytes;
  }

  function getDocumentCount(){
    $this->documents_count;
  }

  function getUpdateTimestamp(){
    $this->update_timestamp;
  }

  function getCreationTimestamp(){
    $this->creation_timestamp;
  }

  function getEmail(){
    $this->email;
  }

  function getId(){
    return $this->id;
  }
  function setOutput($output){
    if($output != "image/png" && $output != "image/jpeg" && $output != "application/pdf"){
      throw new \OpenApi\classes\exception\OpenApiUploaderException("Output format not valid",40013);
    }
    if($output == "image/png" || $output == "image/jpeg") {
      $this->outputGroup = false;
      $this->cropSize = null;
    }
    $this->output = $output;
    $this->checkOutputSize();
  }

  function getOutputSize(){
    return $this->outputSize;
  }

  function getInputCount(){
    return $this->inputCount;
  }

  function getWaterMark(){
    return $this->watermark;
  }

  function setWaterMark($watermark){
    $this->watermark = $watermark;
  }

  function getWaterMarkPosition(){
    return $this->watermarkPosition;
  }
  

  function setWaterMarkPosition($watermarkPosition){
    $this->watermarkPosition = $watermarkPosition;
  }

  function setExpireTimestamp($expireTimestamp){
    if($expireTimestamp == NULL){
      $this->expireTimestamp = NULL;
      return;
    }
    $this->expireTimestamp = (int)$expireTimestamp;
  }

  function getExpireTimestamp(){
    return $this->expireTimestamp;
  }

  function setInputCount($ic){
    if($ic == NULL){
      $this->inputCount = NULL;
      return;
    }
    $this->inputCount = (int)$ic;
  }
  function setOutputGroup($outputGroup){
    if($outputGroup && ($this->output == "image/png" || $this->output == "image/jpeg")){
      throw new \OpenApi\classes\exception\OpenApiUploaderException("Output group not valid",40015);
    }
    $this->outputGroup = $outputGroup;
  }

  function getOutputGroup(){
    return $this->outputGroup;
  }

  function setPublic($public = true){
    $this->public = $public;
  }

  function setPrivate($private = TRUE){
    $this->public = !$private;
  }

  function isPublic(){
    return $this->public;
  }

  function isPrivate(){
    return !$this->public;
  }

  function setInputTypes($types){
    if(!is_array($types)){
      $types = [$types];
    }
    foreach($types as $t){
      if($t != "image/png" && $t != "image/jpeg" && $t != "application/pdf"){
        throw new \OpenApi\classes\exception\OpenApiUploaderException("Input type not valid",40016);
      }
    }
    $this->inputTypes = $types;
  }

  function getInputTypes(){
    return $this->inputTypes;
  }

  function setOutputSize($outputSize){
    $this->outputSize = $outputSize;
    $this->checkOutputSize();
  }

  private function checkOutputSize(){
    if($this->output == null || $this->outputSize == NULL){
      return TRUE;
    }
    if($this->output == "application/pdf"){
      if(!in_array($this->outputSize,$this->validPdfFormats)){
        throw new \OpenApi\classes\exception\OpenApiUploaderException("Output size not valid",40014);
      }
    }else{
      if(!is_array($this->outputSize) || !isset($this->outputSize[0])  || !isset($this->outputSize[1]) || !is_int($this->outputSize[0]) || !is_int($this->outputSize[1])){
        throw new \OpenApi\classes\exception\OpenApiUploaderException("Output size not valid",40014);
      }
    }
  }

  function setOutputJpeg(){
    $this->setOutput("image/jpeg");
  }

  function setOutputPng(){
    $this->setOutput("image/png");
  }

  function setOutputPdf(){
    $this->setOutput("application/pdf");
  }

  function getOutput(){
    return $this->output;
  }

  function addDocument($name, $type,$file,$crop_size = null){
    if($this->id == NULL){
      throw new \OpenApi\classes\exception\OpenApiUploaderException("Impossible to add File",40019);
    }

    $data['name'] = $name;
    $data['type'] = $type;
    $data['file'] = $file;
    $data['crop_size'] = $crop_size;

    $ret = call_user_func_array ($this->connect,["collections/$this->id","POST",$data]);

    $this->updateCollection();
    return $ret->id;
  }

  function updateDocument($id_documento, $name = NULL,$type = NULL, $file = NULL, $crop_size = NULL){
    if($this->id == NULL){
      throw new \OpenApi\classes\exception\OpenApiUploaderException("Impossible to add File",40019);
    }
    if($name != NULL){
      $data['name'] = $name;
    }
    if($type != NULL){
      $data['type'] = $type;  
    }
    if($file != NULL){
      $data['file'] = $file;
    }
    if($crop_size != NULL){
      $data['crop_size'] = $crop_size;
    }

    $ret = call_user_func_array ($this->connect,["collections/$this->id/$id_documento","PATCH",$data]);

    $this->updateCollection();
    return $ret->success;
  }

  function deleteDocument($id_documento){
    if($this->id == NULL){
      throw new \OpenApi\classes\exception\OpenApiUploaderException("Impossible to add File",40019);
    }


    $ret = call_user_func_array ($this->connect,["collections/$this->id/$id_documento","DELETE"]);

    $this->updateCollection();
    return $ret->success;
  }

  private function  updateCollection(){
    
    $coll =  call_user_func_array ($this->connect,["collections/$this->id","GET"]);
    if(isset($coll->data)){
      $this->parseData($coll->data);
          
    }
  }

  function getDocumento($id_documento){
    if($this->id == NULL){
      throw new \OpenApi\classes\exception\OpenApiUploaderException("Impossible to add File",40019);
    }


    $ret = call_user_func_array ($this->connect,["collections/$this->id/$id_documento","GET"]);

    $this->updateCollection();
    return $ret;
  }
}