<?php namespace OpenApi;

class OpenApi {

  
  /**
   * @param array $scopes              Array con gli scopes da utilizzare nel formato: ["domain"=>"ws.ufficiopostale.com", "method"=>"comuni","mode"=>"GET"] oppure "GET:ws.ufficiopostale.com/comuni NOTA: il dominio NON deve mai avere lo stage
   * @param string $username           Username openapi
   * @param string $apikey             ApiKey openapi
   * @param mixed $environment='test'  uno tra: dev, test (default), production  
   */
  function __construct(array $scopes, string $username, string $apikey, $environment='test', $store = NULL){
    if($store == NULL)  {
      $store = new \OpenApi\classes\utility\sessionStoreToken;
    }
    $this->cache = new \OpenApi\classes\utility\DummyCache;
    $this->store = $store;
    $this->header = null;
    $this->rawResponse = null;
    $realScopes = [];
    $domainsRealScopes = [];
    $prefix = $environment=="production"?"":$environment.".";
    $domains = [];
    //var_dump($scopes);exit;
    foreach($scopes as $s){
      if($s == NULL){
        continue;
      }
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
        $domainsRealScopes[$domain] = [];
      }
      
      if(!in_array($realScope,$realScopes)){
        $realScopes[] = $realScope;
        $domainsRealScopes[$domain][] = $realScope;
      }
      
    }
    
    $this->username = $username;
    $this->apikey = $apikey;
    $this->prefix = $prefix;
    $this->scopes = $realScopes;
    $token = $this->getToken();
    
    list($moduli,$nomi) = $this->getListaModuli();
    $this->clients = [];
    foreach($domains as $d){
      if(isset($moduli[$d])){
        $modulo = $moduli[$d];
        $nome = $nomi[$d];
        $this->$nome = new $modulo($token->token, $domainsRealScopes[$d], $this->cache, $prefix);
        $this->clients[] = $nome;
      }
    }

    $this->validations = new \OpenApi\classes\utility\Plugins\Validations();
    $this->fiscalCode = new \OpenApi\classes\utility\Plugins\FiscalCode();
    //$this->geocoding = new \OpenApi\classes\Geocoding($token->token, [], $this->cache, "");
  }

    /**
     * 
     * Restituisce la lista dei moduli disponibili
     * 
     * @return array
     */
    private function getListaModuli(){
      $moduli = [];
      $nomi = [];
      $moduli['ws.ufficiopostale.com'] = "\\OpenApi\\classes\\UfficioPostale";
      $nomi['ws.ufficiopostale.com'] = "ufficiopostale";
      $moduli['imprese.altravia.com'] = "\\OpenApi\\classes\\Imprese";
      $nomi['imprese.altravia.com'] = "imprese";

      $moduli['visengine2.altravia.com'] = "\\OpenApi\\classes\\VisEngine";
      $nomi['visengine2.altravia.com'] = "visengine";


      $moduli['comuni.openapi.it'] = "\\OpenApi\\classes\\Comuni";
      $nomi['comuni.openapi.it'] = "comuni";


      $moduli['ws.marchetemporali.com'] = "\\OpenApi\\classes\\MarcheTemporali";
      $nomi['ws.marchetemporali.com'] = "marcheTemporali";


      $moduli['geocoding.realgest.it'] = "\\OpenApi\\classes\\Geocoding";
      $nomi['geocoding.realgest.it'] = "geocoding";

      $moduli['ws.messaggisms.com'] = "\\OpenApi\\classes\\Sms";
      $nomi['ws.messaggisms.com'] = "SMS";


      $moduli['ws.firmadigitale.com'] = "\\OpenApi\\classes\\FirmaDigitale";
      $nomi['ws.firmadigitale.com'] = "firmaDigitale";

      $moduli['ws.pecmassiva.com'] = "\\OpenApi\\classes\\PecMassiva";
      $nomi['ws.pecmassiva.com'] = "pecMassiva";
      return array($moduli,$nomi);
    }
  
  /**
   * Imposta la calsse da utilizzare sistema di cache, deve essere una classe che estende
   * {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   * 
   * @param mixed $cacheSys Istanza della classe da usare come sistema di cache
   * @return void
   */
  function setCacheSystem($cacheSys){
    $this->cache = $cacheSys;
    foreach($this->clients as $c){
      $this->$c->setCacheSystem($cacheSys);
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
      
      if(!$this->mustRfreshToken()){
        return $this->store->get()['token'];
      }
      $this->renewToken();
     
      return $this->store->get()['token'];
    }
    if($this->getOldToken()){
      if(!$this->mustRfreshToken()){
        return $this->store->get()['token'];
      }
      $this->renewToken();
      return $this->store->get()['token'];
    }
    return $this->generateNewToken();
  }

    
  /**
   * Rinnova il token in sessione
   *
   * @return object
   */
  private function renewToken(){
    $param = ["expire" => time() + 86400, "scopes" => $this->scopes];
    //var_dump($param);exit;

    $token = $this->connect("token/".$this->store->get()['token']->token,$param,"PUT");

    if($token == NULL){
      throw new \OpenApi\classes\exception\OpenApiTokenException("Renew Token: Connection Error",40001);
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
      $this->store->get()['token'] = $token;
      return $token;
    }
    
  }

    
  /**
   * Controlla se il token in sessione deve essere o meno rinnovato in base alla sua data di scadenza
   *
   * @return bool
   */
  private function mustRfreshToken(){
    $token = $this->store->get()['token'];
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
    $param = ["scope" => $this->scopes];
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
        $tostore['token'] = $finded_token;
        $tostore['apikey'] = $this->apikey;
        $tostore['scopes'] = serialize($this->scopes);
        $tostore['username'] = $this->username;
        $tostore['prefix'] = $this->prefix;
        $this->store->save($tostore);
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
    $tostore['token'] = $token;
    $tostore['apikey'] = $this->apikey;
    $tostore['scopes'] = serialize($this->scopes);
    $tostore['username'] = $this->username;
    $tostore['prefix'] = $this->prefix;

    $this->store->save($tostore);

    return $token;
  }

  
  /**
   * 
   * Constrolla se il token in sessione è compatibile con la richiesta
   *
   * @return boolean
   */
  private function isTokenCompatible() {
    if(!$this->store->isset()|| !isset($this->store->get()['token'])){
      return TRUE;
    }
    if($this->store->get()['prefix'] != $this->prefix || $this->store->get()['apikey'] != $this->apikey  || $this->store->get()['username'] != $this->username){
      return TRUE;
    }
    $sessionScopes = unserialize($this->store->get()['scopes']);
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
    if($mode == "GET")
    {
      $param = http_build_query($param);
      $param = preg_replace('/(%5B)\d+(%5D=)/i', '$1$2', $param);
      $url .= "?".$param;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode);
    if($mode == "POST" || $mode == "PUT")
		{
			curl_setopt($ch, CURLOPT_POST, TRUE); 
    }
    if($mode != "GET")
    {
      $param = json_encode($param);
      
      curl_setopt($ch, CURLOPT_POSTFIELDS, $param); 
    }

    $baseauth = base64_encode($this->username.":".$this->apikey);
    $headers = array(
      'Content-Type:application/json',
      'Authorization: Basic '. $baseauth
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
