<?php 
namespace OpenApi\classes\exception;

/**
 *  Gestisce le eccezioni relative alle funzionalità ufficiopostale
 * 40011: si sta tentando di confermare un prodotto postale non ancora inviato
 * 40012: si sta tentando di confermare un prodotto postale che non è nello stato di NEW
 */
class OpenApiUPException extends OpenApiExceptionBase
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}