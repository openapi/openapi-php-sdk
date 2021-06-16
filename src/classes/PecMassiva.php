<?php
namespace OpenApi\classes;
class PecMassiva extends OpenApiBase {

  private string $username;
  private string $password;
  private bool $inizialized;
  


  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://ws.pecmassiva.com";
    $this->inizialized = FALSE;
    
  }

  function initialize(string $username, string $password){
    $this->username = $username;
    $this->password = $password;
    $this->inizialized = TRUE;
  }

  function getStatus($messageId){
    if(!$this->inizialized){
      throw new \OpenApi\classes\exception\OpenApiPecMassivaException("class must initialized calling initialize function", 40011);
    }


    try{
      $header[] = 'x-username: '.$this->username;
      $header[] = 'x-password: '.$this->password;
      return $this->connect("send/$messageId","GET",[],0,false,$header);
      
     }catch(\OpenApi\classes\exception\OpenApiConnectionsException $e){
       if(isset($e->getServerResponse()->message_id)){
         throw new \OpenApi\classes\exception\OpenApiPecMassivaException("error occurred connecting to SMTP: ".$e->getServerResponse()->message_id, 40012);
       }
       throw $e;
         
     }
  }

  function send($recipient, $subject, $body, $attachments = [], $sender = NULL){
    if(!$this->inizialized){
      throw new \OpenApi\classes\exception\OpenApiPecMassivaException("class must initialized calling initialize function", 40011);
    }
    $sender = $sender ? $sender : $this->username;
    
    $params['username'] = $this->username;
    $params['password'] = $this->password;
    $params['recipient'] = $recipient;
    $params['subject'] = $subject;
    $params['body'] = $body;
    if(count($attachments)>0){
      $params['attachments'] = $attachments;  
    }
    $params['sender'] = $sender;
    try{
     return $this->connect("send","POST",$params);
    }catch(\OpenApi\classes\exception\OpenApiConnectionsException $e){
      if(isset($e->getServerResponse()->message_id)){
        throw new \OpenApi\classes\exception\OpenApiPecMassivaException("error occurred connecting to SMTP: ".$e->getServerResponse()->message_id, 40012);
      }
      throw $e;
        
    }
    
  }
  

}