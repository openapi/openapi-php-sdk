<?php 
namespace Openapi\classes\exception;

/**
 *  Gestisce le eccezioni relative alle funzionalità SMS
 * 400010: Si è tentato di aggiungere dei recipienti, ma non è presente l'id del messagggio
 */
class OpenapiSMSException extends OpenapiExceptionBase
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}