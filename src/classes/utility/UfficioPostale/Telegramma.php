<?php
namespace OpenApi\classes\utility\UfficioPostale;
class Telegramma extends ServiziPostali {

  protected $letter;
  protected $parole;
  private $ar_e;
  private $ar_c;
  function __construct($connect, $errorClass){
    parent::__construct($connect, $errorClass);
    $this->letter = NULL;
    $this->ar_e = false;
    $this->ar_c = false;
  }

  function getNumeroLettere(){
    return $this->letter;
  }

  function getNumeroParole(){
    return $this->parole;
  }
  function getNumeroPagine(){
    throw new \OpenApi\classes\exception\OpenApiUPException("Pages not exist for telagrammi",40015);
  }


  function confirm(){
    if($this->getId() == NULL){
      throw new \OpenApi\classes\exception\OpenApiUPException("Id not present",40011);
    }
    if($this->getState() != "NEW"){
      throw new \OpenApi\classes\exception\OpenApiUPException("State is not NEW",40012);
    }
    $param['confirmed'] = TRUE;
    $ret = call_user_func_array($this->connect,["telegrammi/".$this->getId(),"PATCH",$param]);
    
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
     
      $object->opzioni->ar_c = is_bool($this->ar_c)?$this->ar_c:$this->ar_c->getObject(TRUE);
      
      $object->opzioni->ar_e = $this->ar_e;

      if($this->getCallback() != NULL){
        $callback = $this->getCallback();
        foreach($callback as $k => $v){
          $object->opzioni->$k = $v;
        }
      }
      $ret = call_user_func_array($this->connect,["telegrammi/","POST",$object]);
      $this->pricing = $ret->data[0]->pricing;
      $this->id = $ret->data[0]->id;
      $this->confirmed = $ret->data[0]->confirmed;
      $this->state = $ret->data[0]->state;
      $this->letter = $ret->data[0]->documento_validato->size;
      $this->parole = $ret->data[0]->documento_validato->parole;
      $this->clearRecipients();
      $this->setRecipients($ret->data[0]->destinatari);
      return true;
    }catch(\OpenApi\classes\exception\OpenApiConnectionsException $e){
      $response = $e->getServerResponse();
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

  function creaTelegrammaByData($data){
    if(isset($data->documento_validato) && is_object($data->documento_validato)){
      $this->valid_doc_pdf = $data->documento_validato->pdf;
      $this->valid_doc_jpg = $data->documento_validato->jpg;
    }
    $this->pricing = $data->pricing;
    $this->id = $data->id;
    $this->confirmed = $data->confirmed;
    $this->state = $data->state;
    $this->letter = $data->documento_validato->size;
    $this->parole = $data->documento_validato->parole;
    $this->clearRecipients();
    $this->setRecipients($data->destinatari);
    $this->setSender($data->mittente);
    $this->ar_e = isset($data->opzioni->ar_e)?$data->opzioni->ar_e:FALSE;
    $this->ar_c = isset($data->opzioni->ar_c)?$data->opzioni->ar_c:FALSE;
   /* $this->colori = $data->opzioni->colori;
    $this->fronteretro = $data->opzioni->fronteretro;
    $this->ar = $data->opzioni->ar;*/
    $this->setCallback($data->opzioni->callback_url, $data->opzioni->custom);
    if(isset($data->IDRichiesta)){
      $this->request_id = $data->IDRichiesta;
    }
    
  }


  function setRicevutaElettronica($ar_e){
    $this->ar_e = $ar_e;
  }

  function getRicevutaElettronica(){
    return $this->ar_e;
  }

  function setRicevutaCartacea($ar_c){
    if(is_bool(($ar_c))){
      $this->ar_c = $ar_c;
      return TRUE;
    }else{
      if($ar_c instanceof \OpenApi\classes\utility\UfficioPostale\Objects\Sender){
        $this->ar_c = $ar_c;
      }else{
        $this->ar_c = new \OpenApi\classes\utility\UfficioPostale\Objects\Sender($ar_c);
      }
      if(!$this->ar_c->validate()){
        return FALSE;
      }
      return TRUE;
    }
    
  }

  function getRicevutaCartacea(){
    return $this->ar_c;
  }

}