<?php 
namespace OpenApi\classes;
class OpenApiBase {
  function __construct($token, $cache){
    $this->token = $token;
    $this->token = $cache;
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
  }

}