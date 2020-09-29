<?php 
namespace OpenApi\classes\exception;

/**
 *  Gestisce le eccezioni relative alle funzionalità marchetemporali
 * 40009: Si sta tentando di comprare un lotto di marche non disponibile
 */
class OpenApiMarcheTemporaliException extends OpenApiExceptionBase
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}