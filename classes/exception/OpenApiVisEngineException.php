<?php 
namespace OpenApi\classes\exception;

/**
 *  Gestisce le eccezioni relative alle funzionalità visengine
 * 40005: Si stata tentando di effettuare una chiamata qualsiasi ma non è stato settato l'hash della visura
 * 40006: Si sta tentando di impostare una chiave per il JSON della visura su una visura per cui quella chiave non esiste
 * 40007: Si è provato ad impostare lo stato della visura ad 1 con il JSON non valido
 * 40008: Si è cercato di instanziare una classe interna esternamente, tali operazioni sono permesso solo utilizzando gli appositi metodi delle singole classi
 */
class OpenApiVisEngineException extends OpenApiExceptionBase
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}