<?php
namespace OpenApi\classes;
class VisEngine extends OpenApiBase {
  
  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    $this->hash = NULL;
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://visengine2.altravia.com";
  }
  
  /**
   *  Imposta l'hash della Visura da utilizzare per tutti i dati
   * @param string $hash
   * 
   */
  function setHash(string $hash){
    $this->hash = $hash;
  }

  function getFormTool(){
    if($this->hash == NULL){
      throw new \OpenApi\classes\exception\OpenApiVisEngineException("Visengine hash is not setted",40005);
    }
    $url = $this->basePath;
    $url = str_replace("https://","https://".$this->prefix,$url);
    $url = str_replace("http://","http://".$this->prefix,$url);
    $url .= "/visura/formTool/{$this->hash}.js";
    return $url;
  }

  
  /**
   * 
   * Prepara un nuovo oggetto di tipo VisRequest da utilizzare per inviare una nuova richiesta e lo restituisce
   * @param int $ttr Time to Release sulla richiesta dei dati della visura
   * 
   * @return object
   */
  function createRequest($ttr = 500){
    if($this->hash == NULL){
      throw new \OpenApi\classes\exception\OpenApiVisEngineException("Visengine hash is not setted",40005);
    }
    $this->visura = $this->connect("visure/$this->hash", "GET", [], $ttr);
    defined("OPENAPI_CREATING_REQUEST") OR define("OPENAPI_CREATING_REQUEST", TRUE);
    return new \OpenApi\classes\utility\VisEngine\VisRequest($this->visura);
  }

  
  /**
   * Invia una request, in base al contenuto della stessa distingue automaticamente se fare la chiamata in POST o PUT
   * Restituisce la richiesa comprensiva di risposta del server
   * @param \OpenApi\classes\utility\VisEngine\VisRequest $request
   * 
   * @return object
   */
  function sendRequest(\OpenApi\classes\utility\VisEngine\VisRequest $req, $new_search = FALSE) {
    
    
   
    if($req->getNew()){
      $params = new \stdClass();
    $params->state = $req->getState();
    $params->test = $req->getTest();
    $params->hash_visura = $this->hash;
    if($req->getJson() != NULL){
      $params->json_visura = $req->getJson();
    }
    if($req->getCallbackData() != NULL){
      $params->callback_data = $req->getCallbackData();
    }
    if($req->getFornitore() != NULL){
      $params->callback = $req->getFornitore();
    }
    if($req->getTargetEmail() != NULL){
      $params->email_target = $req->getTargetEmail();
    }
      $data = $this->connect("richiesta", "POST", $params);
 //var_dump($data);exit;
      $req->setNew(FALSE);
      $req->setId($data->data->_id);
      $req->setStatoRichiesta($data->data->stato_richiesta);
      
      if(isset($data->data->callback)){
        $req->setFornitore($data->data->callback);
      }else{
        $req->setFornitore(NULL);
      }
      if(isset($data->data->ricerche)){
        $req->setRicerche($data->data->ricerche);
      }
      return $req;
    }else{
      
      $params = new \stdClass();
      $params->state = $req->getState();
     // $params->test = $req->getTest();
     $id_visura = $req->getId();
      if($req->getJson() != NULL){
        $params->json_visura = $req->getJson();
      }
    
     
      //echo json_encode($params);exit;
     //var_dump($params);exit;
     $visura = $this->connect("richiesta/$id_visura", "GET");
 
     if(!$new_search)
    {
      $data = $this->connect("richiesta/$id_visura", "PUT", $params);
    }else{
      $data = $this->connect("richiesta/$id_visura", "POST", $params);
    }

 //   var_dump($data);exit;
      
    
     
      $req->setNew(FALSE);
      $req->setId($data->data->_id);
      $req->setStatoRichiesta($data->data->stato_richiesta);
      if(isset($data->data->callback)){
        $req->setFornitore($data->data->callback);
      }
      if(isset($data->data->ricerche)){
        $req->setRicerche($data->data->ricerche);
      }
      return $req;
    }
  }

  function getDatiFornitore($email){
    $data = $this->connect("fornitori/$email", "GET", []);
    return $data;
  }

  function getFornitori(){
    $data = $this->connect("fornitori", "GET", []);
    return $data;

  }

  function getRequestByIdVisura($id_visura){
    $visura = $this->connect("richiesta/$id_visura", "GET");
    
    /*if($visura->data->callback){
      var_dump($visura->data->callback);exit;
    }*/
    
    return $this->getRequestByData($visura);
  }

  function cancelRequest($id_visura, $motivazione){
    $visura = $this->connect("richiesta/$id_visura", "DELETE",["motivo"=>$motivazione]);
    return $this->getRequestByIdVisura($id_visura);
  }
  

  function getRequestByData($visura){
    
    $this->visura = $this->connect("visure/{$visura->data->hash_visura}", "GET", [], 0);
    $this->hash = $visura->data->hash_visura;
    defined("OPENAPI_CREATING_REQUEST") OR define("OPENAPI_CREATING_REQUEST", TRUE);
    $request = new \OpenApi\classes\utility\VisEngine\VisRequest($this->visura);
    $request->setNew(FALSE);
    $request->setId($visura->data->_id);
    $request->setStatoRichiesta($visura->data->stato_richiesta);
    if(isset($visura->data->ricerche)){
      $request->setRicerche($visura->data->ricerche);
    }else{
      $request->setRicerche([]);
    }
    if($request->getStatoRichiesta() == "Dati disponibili" || $request->getStatoRichiesta() == "Visura evasa"){
      $documento = $this->connect("documento/{$visura->data->_id}", "GET", [], 0);
      $request->setDocument($documento->data);
    }
    if($visura->data->stato_richiesta == "Annullata"){
      $request->setReasonCancellation($visura->data->motivo_annullamento);
      
    }
    if(isset($visura->data->callback) && $visura->data->callback){
      $request->setFornitore($visura->data->callback);
    }
    return $request;
  }

  function getDocument($id_visura){
    $request = $this->getRequestByIdVisura($id_visura);
    $documento = $this->connect("documento/{$id_visura}", "GET", [], 0);
    if($request->getStatoRichiesta() == "Dati disponibili" || $request->getStatoRichiesta() == "Visura evasa"){

      $request->setDocument($documento->data);
    }
    return $request;
  }

  function setRicerca($id_visura, $id_ricerca, $index){
    $index = str_replace("indice_","",$index);
    $request = $this->getRequestByIdVisura($id_visura);
    $hash_visura = $request->visura->data->hash_visura;
    $param = ["id_ricerca"=>$id_ricerca, "indice"=> $index];
    
    $visura = $this->connect("richiesta/{$id_visura}/ricerche", "PUT", $param, 0);
    
    $request->setStatoRichiesta($visura->data->stato_richiesta);
    return $request;

  }

  function getCatalog($ttr = 86400){
    return $this->connect("visure", "GET", NULL, $ttr);
  }

}


