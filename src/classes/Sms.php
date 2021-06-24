<?php
namespace OpenApi\classes;
class Sms extends OpenApiBase {
  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://ws.messaggisms.com";
    $this->messageId = NULL;
  }

  function addRecipeints($recipients, $finish = false){
    if($this->messageId == NULL){
      throw new \OpenApi\classes\exception\OpenApiSMSException("No message id presente",40010);
      exit;
    }
    $data = $this->addRecipeintsByMessageId($this->messageId, $recipients, $finish );
    if($finish){
      $this->messageId = NULL;
    }
    return $data;
  }

  function addRecipeintsByMessageId($messageId, $recipients, $finish = false){
    $param['recipients'] = $recipients;
    $param['transaction'] = !$finish;
    try{
      $data = $this->connect("messages/$messageId", "PUT", $param);
      
      return $data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      exit;
    }
  }

  function getRecipients($messageId, $number = NULL){
    try{
      $data = $this->connect("messages/$messageId/recipients/".$number, "GET", []);
      return $data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      exit;
    }
  }

  function getMessage($messageId){
    try{
      $data = $this->connect("messages/$messageId", "GET", []);
      return $data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      exit;
    }
  }

  function sendMore($sender, $recipients, $text, $transaction = false, $priority = 1,$options = NULL, $test = false){
  
    $param['test'] = $test;
    $param['sender'] = $sender;
    $param['recipients'] = $recipients;
    $param['body'] = $text;
    $param['transaction'] = $transaction;
    $param['priority'] = $priority;
    if(sizeof($options)){
      $param['options'] = $options;
    }

    try{
      $data = $this->connect("messages/", "POST", $param);
      if(isset($data->data[0]) && $transaction){
        $this->messageId =$data->data[0]->id;
      }
      return $data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      exit;
    }
  }

  /**
   * @param number $priority un moltiplicatore per la priorita di invio 
   */
  function sendOne($sender, $recipient, $text, $prefix = NULL, $priority = 1,$options = [], $test = false){
    if($prefix != NULL){
      $recipient = $prefix."-".$recipient;
    }
    
    $param['test'] = $test;
    $param['sender'] = $sender;
    $param['recipients'] = $recipient;
    $param['body'] = $text;
    $param['transaction'] = FALSE;
    $param['priority'] = $priority;
    if(sizeof($options)){
      $param['options'] = $options;
    }
    
    try{
      $data = $this->connect("messages/", "POST", $param);
    
      return $data;
    }catch (\OpenApi\classes\exception\OpenApiConnectionsException $e){
      if($e->getHTTPCode() == 404){
        return null;
      }
      throw $e;
      exit;
    }
  }

}