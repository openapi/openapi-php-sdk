<?php
namespace OpenApi\classes\utility\UfficioPostale;
class ServiziPostali {

  protected $connect;
  protected $sender;
  protected $recipients;
  protected $documents;
  protected $textMessage;
  protected $validRecipients;
  protected $fronteretro;
  protected $colori;
  protected $ar;
  protected $autoconfirm;
  protected $pricing;
  protected $id;
  protected $confirmed;
  protected $state;
  protected $callback;

  function __construct($connect){
    $this->connect = $connect;
    $this->sender = NULL;
    $this->recipients = [];
    $this->documents = [];
    $this->textMessage = NULL;
    $this->validRecipients = FALSE;
    $this->fronteretro = FALSE;
    $this->colori = FALSE;
    $this->ar = FALSE;
    $this->autoconfirm = FALSE;
    $this->pricing = FALSE;
    $this->id = FALSE;
    $this->confirmed = FALSE;
    $this->state = FALSE;
    $this->callback = NULL;
  }

  function getPricing(){
    return $this->pricing;
  }

  function getId(){
    return $this->id;
  }

  function getConfirmed(){
    return $this->confirmed;
  }

  function getState(){
    return $this->state;
  }
  function setAutoconfirm($autoconfirm){
    $this->autoconfirm = $autoconfirm;
  }

  function getAutoconfirm(){
    return $this->autoconfirm;
  }

  function setColori($colori){
    $this->colori = $colori;
  }

  function getColori(){
    return $this->colori;
  }
  function setFronteRetro($fronteretro){
    $this->fronteretro = $fronteretro;
  }

  function getFronteRetro(){
    return $this->fronteretro;
  }

  function setAR($ar){
    $this->ar = $ar;
  }

  function getAR(){
    return $this->ar;
  }


  public function setSender($sender){
    if($sender instanceof \OpenApi\classes\utility\UfficioPostale\Objects\Sender){
      $this->sender = $sender;
    }else{
      $this->sender = new \OpenApi\classes\utility\UfficioPostale\Objects\Sender($sender);
    }
    if(!$this->sender->validate()){
    //  var_dump($this->sender->getErrors());
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
      $this->recipients[] = $recipient;
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
    $this->recipients[] = $recipient;
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

  public function setCallback($url, $custom = NULL,$callback_field = NULL){
    $callback = new \stdClass();
    $callback->callback_url = $url;
    $callback->custom = $custom;
    if($callback_field != NULL){
      $callback->callback_field = $callback_field;
    }
    $this->callback = $callback;
  }

  public function getCallback(){
    return $this->callback;
  }
}