<?php 
namespace OpenApi\classes\exception;

/**
 *  Gestisce le eccezioni relative alla richiesta del token, di seguito i codici errore ritornati:
 * 40011: Si è tentato di lanciare una operazione senza aver prima inizializzato la libreria con username e password
 * 40012: C'è stato un errore di connessione al server 
 */
class OpenApiPecMassivaException extends OpenApiExceptionBase
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

}