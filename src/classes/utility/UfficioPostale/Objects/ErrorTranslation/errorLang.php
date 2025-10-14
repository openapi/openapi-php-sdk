<?php
namespace OpenApi\classes\utility\UfficioPostale\Objects\ErrorTranslation;
class errorLang {
  function __construct($json){
    if(strpos($json,".json") === FALSE){
      $json = __DIR__."/lng/$json.json";
    }
    $this->errors = (array)json_decode(file_get_contents($json));
  }
  
  function getError($code, $serverResponse){
    if(isset($this->errors[$code])){
      return $this->errors[$code];
    }else{
      if(isset($serverResponse->message)){
        return $serverResponse->message;
      }
    }

    return NULL;
  }
  
}