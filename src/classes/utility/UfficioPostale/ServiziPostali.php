<?php
namespace OpenApi\classes\utility\UfficioPostale;
class ServiziPostali {

  protected $connect;
  protected $sender;
  protected $recipients;
  protected $documents;
  protected $textMessage;
  protected $validRecipients;

  function __construct($connect){
    $this->connect = $connect;
    $this->sender = NULL;
    $this->recipients = [];
    $this->documents = [];
    $this->textMessage = NULL;
    $this->validRecipients = FALSE;
  }


  public function setSender($sender){
    if($sender instanceof \OpenApi\classes\utility\UfficioPostale\Objects\Sender){
      $this->sender = $sender;
    }else{
      $this->sender = new \OpenApi\classes\utility\UfficioPostale\Objects\Sender($sender);
    }
    if(!$this->sender->validate()){
      return FALSE;
    }
    return TRUE;
  }

  public function getSender(){
    return $this->sender;
  }

  public function getSenderError(){
    if($this->sender == NULL){
      return NULL;
    }
    return $this->sender->getErrors();
  }

  public function getRecpients(){
    return $this->recipients;
  }

  public function getRecpientsErrors(){
    if($this->recipients == NULL){
      return NULL;
    }
    if($this->validRecipients){
      return [];
    }
    $errors = [];
    foreach($this->recipient AS $r){
      $errors[] = $r->getErrors();
    }
    return $errors;
  }

  public function setTextMessage($message){
    $this->textMessage = $message;
  }

  public function setRecipients($recipients){
    $this->clearRecipients();
    $valid = TRUE;
    foreach($recipients as $key => $recipient){
      if(!($recipient instanceof \OpenApi\classes\utility\UfficioPostale\Objects\Recipient)){
      
        $recipient = new \OpenApi\classes\utility\UfficioPostale\Objects\Recipient($recipient);
      }
      if(!$recipient->validate()){
        $valid = FALSE;
      }
      $this->recipient[] = $recipient;
    }
    $this->validRecipients = $valid;
    return $valid;
  }

  public function addRecipient($recipient){
    if(!($recipient instanceof \OpenApi\classes\utility\UfficioPostale\Objects\Recipient)){
      $recipient = new \OpenApi\classes\utility\UfficioPostale\Objects\Recipient($recipient);
    }
    $valid = TRUE;
    if(!$recipient->validate()){
      $valid = FALSE;
    }
    $this->recipient[] = $recipient;
    $this->validRecipients = $valid;
    return $valid;
  }

  public function clearRecipients(){
    $this->recipients = [];
  }

  public function setDocuments($documents){
    $this->documents = $documents;
  }

  public function addDocument($document){
    $this->documents[] = $document;
  }

  public function clearDocuments(){
    $this->documents = [];
  }
}