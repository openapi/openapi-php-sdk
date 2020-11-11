<?php 
namespace OpenApi\classes;
class OpenApiBase {


  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   * @param string $prefix   Prefisso da utilizzare per le chiamate (dev, test o null)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    $this->token = $token;
    $this->cache = $cache;
    $this->scopes = $scopes;
    $this->prefix = $prefix;
    $this->basePath = null;
  }

  /**
   * Imposta la calsse da utilizzare sistema di cache, deve essere una classe che estende
   * {@see \OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   * 
   * @param object $cacheSys Istanza della classe da usare come sistema di cache
   * @return void
   */
  function setCacheSystem(object $cacheSys){
    $this->cache = $cacheSys;
  }



  /**
   * Salva un oggetto nella cache
   * @param string $key     LA chiave utilizzata per salvare l'oggetto nella cache
   * @param object $value   L'oggetto da salvare nella cache
   * @param int $ttr        TEmpo in cui l'oggetto resta in cache
   * 
   * @return void
   */
  protected function setCacheObject(string $key, object $value, int $ttr){
    $ttr += rand ( 0 , 120 ); 
    $this->cache->save($key, $value, $ttr);
  }

  /**
   * 
   * Recupera un oggetto dalla cache se presente
   * 
   * @param string $key   LA chiave da utilizzare per recuperare l'oggetto in cache
   * 
   * @return mixed L'oggetto o NUL se insesistente
   */
  protected function getCacheObject(string $key){
    $cached = $this->cache->get($key);
    if($cached){
      return $cached;
    }
    return false;
  }

  /**
   * 
   * Controlla se si ha lo scope necessario per poter invocare il metodo, in caso contrario scatena un'eccezione
   * 
   * @param string $url
   * 
   * @return void
   */
  private function checkHasScope(string $url, string $type){
    $parsed = parse_url($url);
    $permission = $type.":".$parsed['host'];
    $path = $parsed['path'];
    $path = explode("/", $path);
    if(isset($path[1])){
      $permission .= "/".$path[1];
    }
    if(!in_array($permission, $this->scopes)){
      throw new \OpenApi\classes\exception\OpenApiConnectionsException("Scope missed: $permission",40004);
    }
    
  }

  /**
   * @param string $endpoint   Endpoint da richiamare
   * @param string $type       Tipo di chiamata 
   * @param array $param        Parametri da passare alla chiamata
   * @param int $ttr            Tempo in cui la chiamata resta in cache (0 = no cache)
   * 
   * @return mixed
   */
  public function connect(string $endpoint, $type = "GET", $param = [], $ttr = 0, $force = false, $addHeader = NULL){
    $url = $this->basePath;
    $url = str_replace("https://","https://".$this->prefix,$url);
    $url = str_replace("http://","http://".$this->prefix,$url);
    $url .= "/".$endpoint;
    if(!$force){
      $this->checkHasScope($url, $type);
    }
   
    
    if($type == "GET" && $ttr > 0 && $ret = $this->getCacheObject($url)) {
      return $ret;
    }
    $ch = curl_init(); 
		if($type == "POST" || $type == "PUT") 	{
			curl_setopt($ch, CURLOPT_POST, TRUE); 
    }
    if($param != array()) {
      if($type == "GET") {
        $param = http_build_query($param);
        $url .= "?".$param;

      }else{
        $param = json_encode($param);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param); 
      }
    }
    curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
    $header = array("Authorization: Bearer ".$this->token);
    if($addHeader != NULL && is_array($addHeader) && count($addHeader)>0){
      $header = array_merge($header, $addHeader);
    }
    
    
    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $response = curl_exec($ch);
   // var_dump($response);exit;
    $this->rawResponse = $response;
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $this->header = substr($response, 0, $header_size);
    //var_dump($this->header);exit;
    $this->parsedHEader = $this->parseHeader($this->header);
    $return = substr($response, $header_size);
    $httpCode = curl_getinfo ( $ch, CURLINFO_RESPONSE_CODE );;
    curl_close($ch);
    
    if(isset($this->parsedHEader['Content-Type']) && strtolower($this->parsedHEader['Content-Type']) == "application/json") {
      
      $data =  json_decode($return);
    }else if(json_decode($return) != NULL){
      $data =  json_decode($return);
      
    }else{
      $data = $return;
    }
    
    if($data == NULL){
      throw new \OpenApi\classes\exception\OpenApiConnectionsException("Connection to $url: Connection Error",40001);
    }
    
    if(is_object($data) && $data->success == false){
      $message = "Connection to $url: unknow error";
      if(isset($data->message)) {
        if(is_string(($data->message))){
          if($dataMessage = json_decode($data->message)){
            $data = $dataMessage;
          }
          $message = "Connection to $url: $data->message";
        }else{
          $message = "Connection to $url error";
        }
        
        
      }
      //var_dump($this->rawResponse);
      $except = new \OpenApi\classes\exception\OpenApiConnectionsException($message,40002);
      $except->setServerResponse($data, $this->header, $this->rawResponse, $httpCode);
      
      throw $except;
    }
    if($type == "GET" && $ttr > 0) {
      $this->setCacheObject($url, $data, $ttr);
    }
    return $data;
  }

  private function parseHeader($headers){
    $headers = explode("\n",$headers);
    $parsedHeaders = array();
    foreach ($headers as $header) {
      $header = trim($header);
      
      $header = explode(":",$header);

      if(count($header) < 2){
        
        continue;
      }
      $key = $header[0];
      unset($header[0]);
      $parsedHeaders[trim($key)] = trim(implode(":",$header));
    }
  //  exit;
    
    return $parsedHeaders;
  }

}