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
  protected $pages;
  protected $request_id;
  protected $numero_pagine;
  protected  $valid_doc_pdf;
  protected  $valid_doc_jpg;

  function __construct($connect, $errorFunc){
    $this->connect = $connect;
    $this->sender = NULL;
    $this->errorFunc = $errorFunc;
    $this->recipients = [];
    $this->valid_doc_pdf = NULL;
    $this->valid_doc_jpg = NULL;
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

 
  function setRequestId($request_id){
    $this->request_id = $request_id;
  }

  function getRequestId(){
    return $this->request_id ;
  }

  function getValidatedDocument($type = "pdf"){
    if($type == "pdf"){
      return $this->valid_doc_pdf;
    }
    if($type == "jpg"){
      return $this->valid_doc_jpg;
    }
  }

  function getNumeroLettere(){
    throw new \OpenApi\classes\exception\OpenApiUPException("Letter exist only for telagrammi",40016);
  }

  function getNumeroPagine(){
    return $this->numero_pagine;
  }
  function getPages(){
    return $this->pages;
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

  public function getRecipients(){
    return $this->getRecpients();
  }
  //MANTENUTO PER RETROCOMPATIBILITA'
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

  protected function getError($code, $serverResponse){
    return call_user_func_array($this->errorFunc,[$code, $serverResponse]);
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