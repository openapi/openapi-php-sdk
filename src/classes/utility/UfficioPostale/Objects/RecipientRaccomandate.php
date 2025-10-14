<?php
namespace OpenApi\classes\utility\UfficioPostale\Objects;
class RecipientRaccomandate extends Recipient {
  function __construct($recipient = NULL){
    parent::__construct($recipient);
  }
  public function createFromObject($object){
    if(is_array($object)){
      $object = (object)$object;
    }
    //var_dump($object);Exit;
    $stateType = 'TRANSITORIO';
    $stateDescription = '';
  //  var_dump($object);exit;
    if(isset($object->tracking))
    {
      $object->tracking = (array)$object->tracking;
      if(is_array($object->tracking) && count($object->tracking)>1){
        
        $lastT = $object->tracking[count($object->tracking)-1];

        $stateType = $lastT->definitivo?'DEFINITIVO':'TRANSITORIO';
        $stateDescription = $lastT->descrizione;
        
      }
    }else{
      if(isset($object->stateType)){
        $stateType = $object->stateType;
        $stateDescription = $object->state;
      }
    }
    $this->data->stateType = $stateType;
    $this->data->stateDescription = $stateDescription;
    $this->itData->stateType = $stateType;
    $this->itData->stateDescription = $stateDescription;
    $this->data->title = isset($object->title)?$object->title:(isset($object->titolo)?$object->titolo:NULL);
    $this->data->at = isset($object->at)?$object->at:(isset($object->co)?$object->co:NULL);
    $this->data->firstName = isset($object->firstName)?$object->firstName:(isset($object->nome)?$object->nome:NULL);
    $this->data->secondName = isset($object->secondName)?$object->secondName:(isset($object->cognome)?$object->cognome:NULL);
    $this->data->companyName = isset($object->companyName)?$object->companyName:(isset($object->ragione_sociale)?$object->ragione_sociale:NULL);
    $this->data->dug = isset($object->dug)?$object->dug:NULL;
    $this->data->address = isset($object->address)?$object->address:(isset($object->indirizzo)?$object->indirizzo:NULL);
    $this->data->huoseNumber = isset($object->huoseNumber)?$object->huoseNumber:(isset($object->civico)?$object->civico:NULL);
    $this->data->city = isset($object->city)?$object->city:(isset($object->comune)?$object->comune:NULL);
    $this->data->zip = isset($object->zip)?$object->zip:(isset($object->cap)?$object->cap:NULL);
    $this->data->province = isset($object->province)?$object->province:(isset($object->provincia)?$object->provincia:NULL);
    $this->data->country = isset($object->country)?$object->country:(isset($object->nazione)?$object->nazione:"IT");
    $this->data->email = isset($object->email)?$object->email:NULL;

    $this->data->id = isset($object->id)?$object->id:NULL;
    $this->data->state = isset($object->state)?$object->state:NULL;
    $this->data->IdRicevuta = isset($object->IdRicevuta)?$object->IdRicevuta:NULL;
    $this->data->tracking_code = isset($object->NumeroRaccomandata)?$object->NumeroRaccomandata:NULL;
    $this->data->post_office = isset($object->post_office)?$object->post_office:(isset($object->ufficio_postale)?$object->ufficio_postale:NULL);
    $this->data->post_office_box = isset($object->post_office_box)?$object->post_office_box:(isset($object->casella_postale)?$object->casella_postale:NULL);


    
    $this->itData->ufficio_postale = $this->data->post_office;
    $this->itData->casella_postale = $this->data->post_office_box;
    $this->itData->co = $this->data->at;
    $this->itData->titolo = $this->data->title;
    $this->itData->nome = $this->data->firstName;
    $this->itData->cognome = $this->data->secondName;
    $this->itData->ragione_sociale = $this->data->companyName;
    $this->itData->dug = $this->data->dug;
    $this->itData->indirizzo = $this->data->address;
    $this->itData->civico = $this->data->huoseNumber;
    $this->itData->comune = $this->data->city;
    $this->itData->cap = $this->data->zip;
    $this->itData->provincia = $this->data->province;
    $this->itData->nazione = $this->data->country;
    $this->itData->email = $this->data->email;
    $this->itData->id= $this->data->id;
    $this->itData->state= $this->data->state;

    $this->itData->IdRicevuta = isset($object->IdRicevuta)?$object->IdRicevuta:NULL;
    $this->itData->tracking_code = isset($object->NumeroRaccomandata)?$object->NumeroRaccomandata:NULL;
    
  }
}