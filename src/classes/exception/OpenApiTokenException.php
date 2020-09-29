<?php 
namespace OpenApi\classes\exception;

/**
 *  Gestisce le eccezioni relative alla richiesta del token, di seguito i codici errore ritornati:
 * 40001: Il server non ha risposto o non ha risposto con un JSON valido
 * 40002: Il server ha risposto, tuttavia non è stato possibile recuperare il token (es.: username o apikey errati)
 * 40003: Il server ha risposto con il token, tuttavia uno o più degli scopes richiesti non è stato concesso dal server.
 */
class OpenApiTokenException extends OpenApiExceptionBase
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

}