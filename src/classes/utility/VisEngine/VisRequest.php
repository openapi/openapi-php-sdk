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
    $this->document = NULL;
    $this->format_errror = [];
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
      $this->variables[$k] = $v!=NULL;
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

  function hasSearchResult(){
    return $this->visura->data->ricerca == 1 && $this->getStatoRichiesta() == "In ricerca" && $this->getStatoRicerca() == "Ricerca evasa";
  }

  function getSearchResult(){
    if(count($this->ricerche) == NULL){
      return FALSE;
    }
    return json_decode($this->ricerche[count($this->ricerche) - 1]->json_risultato);
  }
  function getSearchId(){
    if(count($this->ricerche) == NULL){
      return FALSE;
    }
    return $this->ricerche[count($this->ricerche) - 1]->id_ricerca;
  }
  
  function getSearchCount(){
    return count($this->ricerche);
  }

  function setDocument($document){
    $this->document = $document;
  }

  function getDocument(){
    return $this->document;
  }

  function getHash(){
    return $this->visura->data->hash_visura;
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
    if(!$this->validaPresenzaCampi()){
      $this->format_errror = [];
      $this->jsonValido = FALSE;
      return;
    }
    $this->format_errror = [];
    if(!$this->validaFormatoCampi()){
      $this->jsonValido = FALSE;
      return;
    }
    $this->jsonValido = TRUE;

  }

  private function validaFormatoCampi(){
    $error = FALSE;
//    var_dump($this->visura->data->json_struttura->campi);exit;

    //cod_comune
      //cod_provincia
      //codice_fiscale_persona_fisica
    foreach($this->visura->data->json_struttura->campi as $key => $campo){
      if(!isset($this->json->$key)  || $this->json->$key == ""){
        continue;
      }
      if(isset($campo->tipo) && $campo->tipo == 'codice_fiscale_persona_fisica'){
        $val = new \OpenApi\classes\utility\Plugins\Validations();
        if(!$val->italianFiscalCode($this->json->$key)){
          $this->format_errror[$key] = 'codice_fiscale_persona_fisica';
          $error = TRUE;
        }
      }

      if(isset($campo->tipo) && $campo->tipo == 'partita_iva'){
        $val = new \OpenApi\classes\utility\Plugins\Validations();
        if(!$val->italianFiscalCode($this->json->$key) && !$val->italianVat($this->json->$key)){
          $this->format_errror[$key] = 'partita_iva';
          $error = TRUE;
        }
      }

      if(isset($campo->tipo) && $campo->tipo == 'codice_fiscale'){
        $val = new \OpenApi\classes\utility\Plugins\Validations();
        if(!$val->italianFiscalCode($this->json->$key) && !$val->italianVat($this->json->$key)){
          $this->format_errror[$key] = 'codice_fiscale';
          $error = TRUE;
        }
      }
      if(isset($campo->tipo) && $campo->tipo == 'cod_comune'){
        $re = '/^[a-zA-Z]{1}[0-9]{3}$/m';
       
        preg_match_all($re, $this->json->$key, $matches, PREG_SET_ORDER, 0);
        if(count($matches) == 0){
          $this->format_errror[$key] = 'cod_comune';
          $error = TRUE;
        }
      }

      if(isset($campo->tipo) && $campo->tipo == 'cod_provincia'){
        $re = '/^[a-zA-Z]{2}$/m';
       
        preg_match_all($re, $this->json->$key, $matches, PREG_SET_ORDER, 0);
        if(count($matches) == 0){
          $this->format_errror[$key] = 'cod_provincia';
          $error = TRUE;
        }
      }
      if(isset($campo->tipo) && $campo->tipo == 'data_iso8601'){
        $d = \DateTime::createFromFormat("d-m-Y", $this->json->$key);
        
        if(!($d && $d->format('d-m-Y') === $this->json->$key)){
          $this->format_errror[$key] = 'data_iso8601';
          $error = TRUE;
        }
      }
    }
    return !$error;
  }

  private function validaPresenzaCampi(){
    $re = '/\$(\d)+/m';
    $validazione = $this->visura->data->json_struttura->validazione;
    $subst = '$this->variables[\'$0\']';
    $valida = TRUE;
    $validazione = '$valida = ' .preg_replace($re, $subst, $validazione).";";
    eval($validazione);
    return $valida;
  }

  public function getErrors(){
    if(count($this->format_errror) != 0){
      $ret['error_type'] = "format";
      $ret['error'] = $this->format_errror;
      return $ret;
    }
    $this->expr = [];
    $validazione = $this->visura->data->json_struttura->validazione;

    list($validazione, $expr) =$this->createExpression($validazione);
    
   //var_dump($validazione);exit;
    $errori = $this->valida($validazione, $expr);

    $ret['error_type'] = "empty_fields";
    $ret['error'] = $errori;
    return $ret;
    
  }

  private function valida($validazione,$expr, $errori = []){
    //var_dump($this->variables);
    $errori = ["type"=>"and","list"=>[]];
    $and = explode('&&',$validazione);
    foreach($and as $andItem){
      $andItem = trim($andItem);
      $or = explode('||',$andItem);
      if(count($or) == 1){
          $orItem = $or[0];
          if(substr($orItem,0,1)=='$'){
            if(!$this->variables[$orItem]){
              $errori['list'][] = $orItem;
            }
          }else{
            $errors = $this->valida($expr[str_replace("e","",$orItem)], $expr);
            if(count($errors)){

              $errori['list'] = array_merge($errori['list'], $errors['list']);
              
            }
          }
      }else{
        $errore = false;
        $item = array();
        $hasError = true;
        foreach($or as $orItem){
          $orItem = trim($orItem);
          if(substr($orItem,0,1)=='$'){
            $item[] = $orItem;
            if($this->variables[$orItem]){
    
              $hasError = FALSE;
            }
          }else{
            $errors = $this->valida($expr[str_replace("e","",$orItem)], $expr);
            if(count($errors['list'])){
              $item[] = $errors;
            }else{
              $hasError = FALSE;
            }
          }
        }
        
        if($hasError){
          $errori['list'][] = ["type"=>"or","list"=>$item];
        }
      }
    }

    return $errori;
  }

  private function createExpression($validazione, $expr = []){
    $preg = "/\(([$\d&| e]*?)\)/";
    preg_match($preg, $validazione, $matches, PREG_OFFSET_CAPTURE, 0);
    if(isset($matches[0]) && isset($matches[1])){
      $expr[] = $matches[1][0];
      $expn = count($expr)-1;
      $validazione = str_replace($matches[0],"e{$expn}",$validazione);
      return $this->createExpression($validazione, $expr);
    }
    return array($validazione, $expr);
    
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

  function getValidation(){
    return $validazione = $this->visura->data->json_struttura->validazione;;
  }
}