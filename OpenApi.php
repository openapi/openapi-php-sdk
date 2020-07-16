<?php namespace OpenApi;
if (session_status() == PHP_SESSION_NONE) {session_start();}
class OpenApi {

  
  /**
   * @param array $scopes              Array con gli scopes da utilizzare nel formato: ["domain"=>"ws.ufficiopostale.com", "method"=>"comuni","mode"=>"GET"] oppure "GET:ws.ufficiopostale.com/comuni NOTA: il dominio NON deve mai avere lo stage
   * @param string $username           Username openapi
   * @param string $apikey             ApiKey openapi
   * @param mixed $environment='test'  uno tra: dev, test (default), production  
   */
  function __construct(array $scopes, string $username, string $apikey, $environment='test'){
    
    $this->cache = new \OpenApi\classes\utility\DummyCache;
    $this->header = null;
    $this->rawResponse = null;
    $realScopes = [];
    $prefix = $environment=="production"?"":$environment.".";
    $domains = [];
    foreach($scopes as $s){
      if(is_array($s)){
        $domain = $s['domain'];
        $realScope = $s['mode'].":".$prefix.$s['domain']."/".$s['method'];
      }else{
        $realScope = str_replace(":",":{$prefix}", $s) ;
        $domain = explode(":", $s)[1];
        $domain = explode("/", $domain)[0];
      }
      if(!in_array($domain, $domains)){
        $domains[] = $domain;
      }
      if(!in_array($realScope,$realScopes)){
        $realScopes[] = $realScope;
      }
    }
    $this->username = $username;
    $this->apikey = $apikey;
    $this->prefix = $prefix;
    $this->scopes = $realScopes;
    $token = $this->getToken();


    $moduli['ws.ufficiopostale.com'] = "\\OpenApi\\classes\\UfficioPostale";
    $nomi['ws.ufficiopostale.com'] = "ufficiopostale";
    $moduli['imprese.altravia.com'] = "\\OpenApi\\classes\\Imprese";
    $nomi['imprese.altravia.com'] = "imprese";
    $clients = [];
    foreach($domains as $d){
      if(isset($moduli[$d])){
        $modulo = $moduli[$d];
        $nome = $nomi[$d];
        $this->$nome = new $modulo($token->token, $this->cache);
        $clients[] = $this->$nome;
      }
    }
  }

  
  /**
   * Imposta la calsse da utilizzare sistema di cache, deve essere una classe che estende
   * {@see OpenApi\clasess\utility\DummyCache}
   * 
   * @param mixed $cacheSys Istanza della classe da usare come sistema di cache
   * @return void
   */
  function setCacheSystem($cacheSys){
    $this->cache = $cacheSys;
    foreach($this->clients as $c){
      $c->setCacheSystem($cacheSys);
    }
  }
  
  
  /**
   * 
   * Restituisce il token attualemnte in sessione, se non presente o non più valido lo rigenera
   *
   * @param boolean $force=FALSE Se impostato a TRUE forza la rigenerazione del token
   * @return object il token
   */
  function getToken($force=FALSE){
    if(!$force && !$this->isTokenCompatible()){
      
      //TODO: Controllare se il token è ancora valido
      if(!$this->mustRfreshToken()){
        return $_SESSION['openapi']['token'];
      }
      $this->renewToken();
     
      return $_SESSION['openapi']['token'];
    }
    if($this->getOldToken()){
      if(!$this->mustRfreshToken()){
        return $_SESSION['openapi']['token'];
      }
      $this->renewToken();
      return $_SESSION['openapi']['token'];
    }
    return $this->generateNewToken();
  }

    
  /**
   * Rinnova il token in sessione
   *
   * @return object
   */
  private function renewToken(){
    $param = ["expire" => 86400, "scopes" => $this->scopes];
    //var_dump($param);exit;

    $token = $this->connect("token/".$_SESSION['openapi']['token']->token,$param,"PUT");

    if($token == NULL){
      throw new \OpenApi\classes\exception\OpenApiTokenException("REnew Token: Connection Error",40001);
    }
    if($token->success == false){
      $message = "REnew Token: unknow error";
      if(isset($token->message)) {
        $message = "REnew Token: $token->message";
      }
      $except = new \OpenApi\classes\exception\OpenApiTokenException($message,40002);
      $except->setServerResponse($token, $this->header, $this->rawResponse);
      
      throw $except;
    }
    if(isset($token->data) && isset($token->data[0]))
    {
      $token = $token->data[0];
      $_SESSION['openapi']['token'] = $token;
      return $token;
    }
    
  }

    
  /**
   * Controlla se il token in sessione deve essere o meno rinnovato in base alla sua data di scadenza
   *
   * @return bool
   */
  private function mustRfreshToken(){
    $token = $_SESSION['openapi']['token'];
    $diff = $token->expire-date("U");
    if($diff <= 6000){
      return TRUE;
    }
    return FALSE;
  }

  
  /**
   * 
   * Recupera la lista di token per verificare se esiste uno utilizzabile con gli scopes di interesse,
   * se si lo mette in sessione e ritorna TRUE
   * 
   * @return boolean
   */
  function getOldToken(){
    $param = ["scopes" => $this->scopes];
    $token = $this->connect("token",$param,"GET");
    $finded_token = NULL;

    if($token != NULL && isset($token->data)){
      foreach($token->data AS $token){
        if($this->hasValidScopes($token)){
          $finded_token = $token;
          break 1;
        }
      }
      
      if($finded_token != NULL){
        $_SESSION['openapi']['token'] = $finded_token;
        $_SESSION['openapi']['apikey'] = $this->apikey;
        $_SESSION['openapi']['scopes'] = serialize($this->scopes);
        $_SESSION['openapi']['username'] = $this->username;
        $_SESSION['openapi']['prefix'] = $this->prefix;
        return TRUE;
      }
      return FALSE;
    }
  }

  function hasValidScopes($token){
    foreach($this->scopes as $s){
      if(!in_array($s, $token->scopes)){
        return false;
      }
    }
    return true;
  }

  /**
   * Genera un nuovo token
   * @return  object il token
   */
  private function generateNewToken(){
    $param = ["scopes" => $this->scopes];
    $token = $this->connect("token",$param,"POST");
    if($token == NULL){
      throw new \OpenApi\classes\exception\OpenApiTokenException("Getting Token: Connection Error",40001);
    }
    if($token->success == false){
      $message = "Getting Token: unknow error";
      if(isset($token->message)) {
        $message = "Getting Token: $token->message";
      }
      $except = new \OpenApi\classes\exception\OpenApiTokenException($message,40002);
      $except->setServerResponse($token, $this->header, $this->rawResponse);
      
      throw $except;
    }

    $invalid_scopes = [];
    foreach($this->scopes as $s){
      if(!in_array($s, $token->scopes)){
        $invalid_scopes[] = $s;
      }
    }
    if(count($invalid_scopes)>0){
      $message = "Getting Token: unknow error";
      if(isset($token->message)) {
        
      }
      $message = "Getting Token: invalid scopes (".implode($invalid_scopes).")";
      $except = new \OpenApi\classes\exception\OpenApiTokenException($message,40003);
      $except->setServerResponse($token, $this->header, $this->rawResponse);
      throw $except;
    }
    $_SESSION['openapi']['token'] = $token;
    $_SESSION['openapi']['apikey'] = $this->apikey;
    $_SESSION['openapi']['scopes'] = serialize($this->scopes);
    $_SESSION['openapi']['username'] = $this->username;
    $_SESSION['openapi']['prefix'] = $this->prefix;
    return $token;
  }

  
  /**
   * 
   * Constrolla se il token in sessione è compatibile con la richiesta
   *
   * @return boolean
   */
  private function isTokenCompatible() {
    if(!isset($_SESSION['openapi'])|| !isset($_SESSION['openapi']['token'])){
      return TRUE;
    }
    if($_SESSION['openapi']['prefix'] != $this->prefix || $_SESSION['openapi']['apikey'] != $this->apikey  || $_SESSION['openapi']['username'] != $this->username){
      return TRUE;
    }
    $sessionScopes = unserialize($_SESSION['openapi']['scopes']);
    if(!is_array($sessionScopes)){
      return TRUE;
    }
    foreach($this->scopes as $s){
      if(!in_array($s, $sessionScopes)){
        return TRUE;
      }
    }
    return FALSE;
  }

  
  /**
   * Effettua una connessione al server oauth
   *
   * @param  string $endpoint path da recuperare
   * @param  array $param Lista dei parametri da passare
   * @param  mixed $mode metodo http da usare per la chiamata
   * @return object
   */
  private function connect(string $endpoint, $param = [], $mode="POST"){
    
    $this->header = null;
    $this->rawResponse = null;
    $basePath = "https://".$this->prefix."oauth.altravia.com";
    $url = $basePath."/".$endpoint;
    

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode);
    if($mode == "POST" || $mode == "PUT")
		{
			curl_setopt($ch, CURLOPT_POST, TRUE); 
    }
    if($mode == "GET")
    {
      $param = http_build_query($param);
      $url .= "?".$param;

    }else{
      $param = json_encode($param);
      
      curl_setopt($ch, CURLOPT_POSTFIELDS, $param); 
    }

    $baseauth = base64_encode($this->username.":".$this->apikey);
    $headers = array(
      'Content-Type:application/json',
      'Authorization: Basic '. $baseauth // <---
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    

    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $response = curl_exec($ch);
    $this->rawResponse = $response;
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $this->header = substr($response, 0, $header_size);
    $return = substr($response, $header_size);
   
    curl_close($ch);
    return json_decode($return);
  }
}
