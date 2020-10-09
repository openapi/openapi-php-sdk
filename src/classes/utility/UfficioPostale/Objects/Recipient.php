<?php
namespace OpenApi\classes\utility\UfficioPostale\Objects;
class Recipient {
  private $data;
  private $itData;
  private $errors;
  private $validate;

  function __construct($recipient = NULL){
    $this->errors = null;
    $this->data = new \stdClass();
    $this->data->firstName = NULL;
    $this->data->secondName = NULL;
    $this->data->companyName = NULL;
    $this->data->at = NULL;
    $this->data->title = NULL;
    $this->data->dug = NULL;
    $this->data->address = NULL;
    $this->data->huoseNumber = NULL;
    $this->data->city = NULL;
    $this->data->zip = NULL;
    $this->data->province = NULL;
    $this->data->country = "Italia";
    $this->data->email = NULL;


    $this->itData = new \stdClass();
    $this->itData->nome = NULL;
    $this->itData->cognome = NULL;
    $this->itData->ragione_sociale = NULL;
    $this->itData->co = NULL;
    $this->itData->titolo = NULL;
    $this->itData->dug = NULL;
    $this->itData->indirizzo = NULL;
    $this->itData->civico = NULL;
    $this->itData->comune = NULL;
    $this->itData->cap = NULL;
    $this->itData->provincia = NULL;
    $this->itData->nazione = "Italia";
    $this->itData->email = NULL;

    $this->validate = false;

    if($recipient == NULL){
      $this->createFromObject($recipient);
    }
  }

  public function createFromObject($object){
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
    $this->data->country = isset($object->country)?$object->country:(isset($object->nazione)?$object->nazione:"Italia");
    $this->data->email = isset($object->email)?$object->email:NULL;

    
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

  }

  public function getObject($itNames = FALSE){
    if(!$itNames){
      return $this->data;
    }
    return $this->itData;
  }

  public function getTitle(){
    return $this->data->title;
  }

  public function getAt(){
    return $this->data->at;
  }

  public function getFirstName(){
    return $this->data->firstName;
  }
  public function getSecondName(){
    return $this->data->secondName;
  }

  public function getCompanyName(){
    return $this->data->companyName;
  }
  public function getDug(){
    return $this->data->dug;
  }
  public function getAddress(){
    return $this->data->address;
  }
  public function getHuoseNumber(){
    return $this->data->huoseNumber;
  }
  public function getCity(){
    return $this->data->city;
  }
  public function getZip(){
    return $this->data->zip;
  }
  public function getProvince(){
    return $this->data->province;
  }
  public function getCountry(){
    return $this->data->country;
  }
  public function getEmail(){
    return $this->data->email;
  }

  public function setTitle(string $title){
    $this->data->title = $title;
    $this->itData->titolo = $title;
  }

  public function setAt(string $at){
    $this->data->at = $at;
    $this->itData->co = $at;
  }

  public function setFirstName(string $firstName){
    $this->data->firstName = $firstName;
    $this->itData->nome = $firstName;
  }

  public function setSecondName(string $secondName){
    $this->data->secondName = $secondName;
    $this->itData->cognome = $secondName;
  }

  public function setCompanyName(string $companyName){
    $this->data->companyName = $companyName;
    $this->itData->ragione_sociale = $companyName;
  }

  public function setDug(string $dug){
    $this->data->dug = $dug;
    $this->itData->dug = $dug;
  }

  public function setAddress(string $address){
    $this->data->address = $address;
    $this->itData->indirizzo = $address;
  }
  public function setHuoseNumber(string $huoseNumber){
    $this->data->huoseNumber = $huoseNumber;
    $this->itData->civico = $huoseNumber;
  }

  public function setCity(string $city){
    $this->data->city = $city;
    $this->itData->comune = $city;
  }

  public function setZip(string $zip){
    $this->data->zip = $zip;
    $this->itData->cap = $zip;
  }

  public function setProvince(string $province){
    $this->data->province = $province;
    $this->itData->privincia = $province;
  }

  public function setCountry(string $country){
    $this->data->country = $country;
    $this->itData->nazione = $country;
  }

  public function setEmail(string $email){
    $this->data->email = $email;
    $this->itData->email = $email;
  }

  public function getErrors($validate = TRUE){
    if($validate){
      $this->validate();  
    }
    
    return $this->errors;
  }

  public function validate(){
    $this->errors = [];
    if($this->data->zip == NULL){
      $this->errors['zip'] = (object)[
        "code"=> "required",
        "text"=>"zip field is required"
      ];
    } else if($this->data->country == "Italia" || $this->data->country == "Italy"){
      $re = '/^\d{5}$/';
      preg_match($re, $this->data->zip, $matches, PREG_OFFSET_CAPTURE, 0);
      if(count($matches) == 0){
        $this->errors['zip'] =  (object)[
          "code"=> "5_digit",
          "text"=>"Italian ZIP must be 5 digits"
        ];
      }
    }

    if($this->data->city == NULL){
      $this->errors['city'] = (object)[
        "code"=> "required",
        "text"=>"city is required"
      ];
    }

    if($this->data->province == NULL){
      $this->errors['province'] = (object)[
        "code"=> "required",
        "text"=>"province is required"
      ];
    }


    if($this->data->dug == NULL){
      $this->errors['dug'] = (object)[
        "code"=> "required",
        "text"=>"dug is required"
      ];
    }

    if($this->data->address == NULL){
      $this->errors['address'] = (object)[
        "code"=> "required",
        "text"=>"address is required"
      ];
    }

    if($this->data->huoseNumber == NULL){
      $this->errors['huoseNumber'] = (object)[
        "code"=> "required",
        "text"=>"huose number is required"
      ];
    }


    $this->validate = count($this->errors) == 0;
    return $this->validate;
  }
  
}