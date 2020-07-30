<?php
namespace OpenApi\classes\utility\VisEngine;
class VisRequest {


  function __construct($visura)
  {
    if(!defined("OPENAPI_CREATING_REQUEST") || !OPENAPI_CREATING_REQUEST){
      throw new \OpenApi\classes\exception\OpenApiVisEngineException("this class is not externally installable, but must be recovered using the appropriate methods of the class openapi-> visengin",40008);
    }
    $this->visura = $visura;
    $this->variables = [];
    $this->new = TRUE;
    $this->json = NULL;
    $this->jsonValido = FALSE;
    $this->state = 0;
    $this->test = false;
    $this->callback = NULL;
    $this->email_target = NULL;
    $this->id = NULL;
    $this->statoRichiesta = NULL;
    $this->ricerche = [];
    foreach($visura->data->json_struttura->campi as $k => $v){
      $this->variables[$k] = FALSE;
    }
  }

  function setNew(bool $new){
    return $this->new = $new;
  }

  function getNew(){
    return $this->new;
  }
  /**
   * 
   * Imposta il JSON per inviare una nuova visura
   * 
   * @param array $data IL JSON della visura da inviare
   * 
   * @return boolean Ritorna TRUE nel caso in cui tutti i campi richiesti per la visura sono stati compilati (attenzione, viene effettuata la sola validazione sui required, per la validazione del formato occcorre inviare la visura)
   */
  function setJson(object $data){
    foreach($data as $k => $v){
      if(!isset($this->variables[$k])){
        throw new \OpenApi\classes\exception\OpenApiVisEngineException("Visengine you are setting $k json key, but $k key is not presente for {$this->visura->data->nome_visura}",40006);
      }
      $this->variables[$k] = TRUE;
    }
    $this->json = $data;
    
    $this->validaJSON();
    return $this->jsonValido;
  }



  /**
   * 
   * Restituisce il JSON della visura
   * 
   * @return object
   */
  function getJson(){
    return $this->json;
  }

  /**
   * Ritorna TRUE nel caso in cui tutti i campi richiesti per la visura sono presenti all'interno del JSON
   * @return boolean
   */
  function isValidJson(){
    return $this->jsonValido;
  }

  /**
   * 
   * Imposta i dati della callback
   * 
   * @param string $url                   La url da richiamare per la callback
   * @param object $data                  Oggetto data che verrà ripassato
   * @param string $method='JSON'         Metodo da usare JSON/POST
   * @param string $field="visengineData" Nome del campo dove verranno passati i dati della visura
   * 
   * @return void
   */
  function setCallbackData(string $url, object $data, $method = "JSON", $field="visengineData"){
    $this->callback = (object)[
      "url" => $url,
      "data" => $data,
      "method" => $method,
      "field" => $field
    ];
  }
  

  /**
   * Restituisce i dati della callback
   * 
   * @return object
   */
  function getCallbackData(){
    return $this->callback;
  }

  /**
   * Impowsta il parametro state della visura
   * @param int $stato
   * 
   * @return void
   */
  function setState($stato = 0){
    if($stato != 0 && !$this->jsonValido ){
      throw new \OpenApi\classes\exception\OpenApiVisEngineException("JSON is not valid, so is not possible set state = 1",40007);
    }
    $this->state = $stato == 0 ? $stato : 1; 
  }

  /**
   * Ritorna il parametro state
   * @return int
   */
  function getState(){
    return $this->state;
  }

  /**
   * Imposta il parametro email_target
   * 
   * @param string $email_target
   * 
   * @return void
   */
  function setTargetEmail(string $email_target){
    $this->email_target = $email_target;
  }

  /**
   * Ritorna il parametro email_target
   * @return string
   */
  function getTargetEmail(){
    return $this->email_target;
  }

  
  /**
   * 
   * Imposta il parametro test
   * 
   * @param bool $test
   * 
   * @return void
   */
  function setTest(bool $test){
    $this->test = $test;
  }

  
  /**
   * Restituisce il parametro test
   * @return bool
   */
  function getTest(){
    return $this->test;
  }


  /**
   * Controlla il JSON e ritorna TRUE nel caso in cui tutti i campi richiesti per la visura sono presenti all'interno del JSON
   * @return boolean
   */
  private function validaJSON(){
    $re = '/\$(\d)+/m';
    $validazione = $this->visura->data->json_struttura->validazione;
    $subst = '$this->variables[\'$0\']';

    $validazione = '$this->jsonValido = ' .preg_replace($re, $subst, $validazione).";";
    eval($validazione);
  }

	function getId() { 
 		return $this->id; 
	} 

	function setId($id) {  
		$this->id = $id; 
	} 

	function getStatoRichiesta() { 
 		return $this->statoRichiesta; 
	} 

	function setStatoRichiesta($statoRichiesta) {  
		$this->statoRichiesta = $statoRichiesta; 
	} 

	function getRicerche() { 
 		return $this->ricerche; 
	} 

	function setRicerche($ricerche) {  
		$this->ricerche = $ricerche; 
  } 
  
  function getStatoRicerca(){
    if(count($this->ricerche) == NULL){
      return FALSE;
    }
    return $this->ricerche[count($this->ricerche) - 1]->stato_ricerca;
  }
}