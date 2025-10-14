<?php
namespace OpenApi\classes\utility\UfficioPostale;
class PostaOrdinaria extends ServiziPostali {

  function __construct($connect, $errorClass){
    parent::__construct($connect, $errorClass);
  }

  function confirm(){
    if($this->getId() == NULL){
      throw new \OpenApi\classes\exception\OpenApiUPException("Id not present",40011);
    }
    if($this->getState() != "NEW"){
      throw new \OpenApi\classes\exception\OpenApiUPException("State is not NEW",40012);
    }
    $param['confirmed'] = TRUE;
    $ret = call_user_func_array($this->connect,["ordinarie/".$this->getId(),"PATCH",$param]);
    
    $this->confirmed = $ret->data[0]->confirmed;
    $this->state = $ret->data[0]->state;
    $this->clearRecipients();
    $this->setRecipients($ret->data[0]->destinatari);
  }

  function send(){
    try{
      $object = new \stdClass();;
      $object->mittente = $this->sender->getObject(TRUE);
      $object->destinatari = [];
      foreach($this->getRecpients() as $r){
        $object->destinatari[] = $r->getObject(TRUE);
      }
      $object->documento =$this->documents;
      $object->opzioni = new \stdClass();
      $object->opzioni->fronteretro = $this->getFronteRetro();
      $object->opzioni->colori = $this->getColori();
      $object->opzioni->autoconfirm = $this->getAutoconfirm();
      if($this->getCallback() != NULL){
        $callback = $this->getCallback();
        foreach($callback as $k => $v){
          $object->opzioni->$k = $v;
        }
      }
      $ret = call_user_func_array($this->connect,["ordinarie/","POST",$object]);
      $this->pricing = $ret->data[0]->pricing;
      $this->id = $ret->data[0]->id;
      $this->confirmed = $ret->data[0]->confirmed;
      $this->state = $ret->data[0]->state;
      $this->pages = $ret->data[0]->documento_validato->pagine;
      $this->clearRecipients();
      $this->setRecipients($ret->data[0]->destinatari);
      return true;
    }catch(\OpenApi\classes\exception\OpenApiConnectionsException $e){
      $response = $e->getServerResponse();
      //var_dump($response->data->wrong_fields);exit;
      if(isset($response->data->wrong_fields) && isset($response->error)){
        $error_message = $this->getError($response->error, $response);
      
        $e2 = new \OpenApi\classes\exception\OpenApiUPFieldsException("Fields Error",40013);
        $e2->setServerResponse($e->getServerResponse(), $e->getServerHeaderResponse(), $e->getServerRawResponse(), $e->getHttpCode());
        $e2->setErrorMessage($error_message);
        $e2->setFields($response->data->wrong_fields);
        throw $e2;
      }
      throw $e;
    }
  }


  function creaPostaOrdinariaByData($data){
    if(isset($data->documento_validato) && is_object($data->documento_validato)){
      $this->valid_doc_pdf = $data->documento_validato->pdf;
      $this->valid_doc_jpg = $data->documento_validato->jpg;
    }
    $this->pricing = $data->pricing;
    $this->id = $data->id;
    $this->confirmed = $data->confirmed;
    $this->state = $data->state;
    $this->numero_pagine = $data->documento_validato->pagine;
    $this->clearRecipients();
    $this->setRecipients($data->destinatari);
    $this->setSender($data->mittente);
    $this->colori = $data->opzioni->colori;
    $this->fronteretro = $data->opzioni->fronteretro;
    $this->setCallback($data->opzioni->callback_url, $data->opzioni->custom);
    
    if(isset($data->IDRichiesta)){
      $this->request_id = $data->IDRichiesta;
    }
    
  }
}