<?php
namespace OpenApi\classes\utility\UfficioPostale;
class Raccomandata extends ServiziPostali {

  function __construct($connect){
    parent::__construct($connect);
  }

  function confirm(){
    if($this->getId() == NULL){
      throw new \OpenApi\classes\exception\OpenApiUPException("Id not present",40011);
    }
    if($this->getState() != "NEW"){
      throw new \OpenApi\classes\exception\OpenApiUPException("State is not NEW",40012);
    }
    $param['confirmed'] = TRUE;
    $ret = call_user_func_array($this->connect,["raccomandate/".$this->getId(),"PATCH",$param]);
    
    $this->confirmed = $ret->data[0]->confirmed;
    $this->state = $ret->data[0]->state;
    $this->clearRecipients();
    $this->setRecipients($ret->data[0]->destinatari);

  }
  
  function creaRaccomandataByData($data){
    
    
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
    $this->ar = $data->opzioni->ar;
    $this->setCallback($data->opzioni->callback_url, $data->opzioni->custom);
  }

  function send(){
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
    $object->opzioni->ar = $this->getAR();
    $object->opzioni->autoconfirm = $this->getAutoconfirm();
    if($this->getCallback() != NULL){
      $callback = $this->getCallback();
      foreach($callback as $k => $v){
        $object->opzioni->$k = $v;
      }
    }
   // var_dump($object);exit;
   if($this->getId() == NULL){
    $ret = call_user_func_array($this->connect,["raccomandate/","POST",$object]);
   }else{
    $ret = call_user_func_array($this->connect,["raccomandate/".$this->getId(),"PATCH",$object]);
   }
    
    if(!isset($ret->data[0])){
      return false;
    }
    $this->pricing = $ret->data[0]->pricing;
    $this->id = $ret->data[0]->id;
    $this->confirmed = $ret->data[0]->confirmed;
    $this->state = $ret->data[0]->state;
    $this->numero_pagine = $ret->data[0]->documento_validato->pagine;
    $this->clearRecipients();
    $this->setRecipients($ret->data[0]->destinatari);
    return true;
    //$ret= $this->connect->call($this,"raccomandate",$object,"POST");

  }
}