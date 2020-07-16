<?php 
namespace OpenApi\classes\exception;
class OpenApiExceptionBase extends \Exception
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
    $this->serverData = NULL;
    $this->serverHeader = NULL;
    $this->serverRawResponse = NULL;
  }
  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }


  /**
   * 
   * Imposta alcune variabili utili in fase di debugging degli errori
   * 
   * @param object $serverData          Response del server già decodato dalla versione json
   * @param string $serverHeader        Stringa contentene gli header della response
   * @param string $serverRawResponse   Stringa contente la risposta raw del server
   * 
   * @return [type]
   */
  function setServerResponse(object $serverData, string $serverHeader, string $serverRawResponse){
    $this->serverData = $serverData;
    $this->serverHeader = $serverHeader;
    $this->serverRawResponse = $serverRawResponse;
  }

  /**
   * 
   * Restituisce la risposta del server già decodata da json
   * 
   * @return object
   */
  function getServerResponse(){
    return $this->serverData;
  }

  /**
   * 
   * Restituisce gli header della rispopsta del server
   * 
   * @return string
   */
  function getServerHeaderResponse(){
    return $this->serverHeader;
  }

  /**
   * 
   * Restituisce la risposta in formato RAW del server
   * @return string
   */
  function getServerRawResponse(){
    return $this->serverRawResponse;
  }
}