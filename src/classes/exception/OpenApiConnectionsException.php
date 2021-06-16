<?php 
namespace OpenApi\classes\exception;

/**
 *  Gestisce le eccezioni relative alla richiesta del token, di seguito i codici errore ritornati:
 * 40001: Il server non ha risposto o non ha risposto con un JSON valido
 * 40002: Il server ha risposto, tuttavia non è stato possibile recuperare i dati in modo corretto
 * 40004: Si sta tentando di effettuare una chiamata per la quale non si hanno i permessi
 
 */
class OpenApiConnectionsException extends OpenApiExceptionBase
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

}