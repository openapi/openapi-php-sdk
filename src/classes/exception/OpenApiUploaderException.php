<?php 
namespace OpenApi\classes\exception;

/**
 *  Gestisce le eccezioni relative alle funzionalità Uploader
 * 400013: Si è tentato di impostare un output diverso da uno di quelli utilizzabili
 * 400014: Si è tentato di impostare un output size non compatibile con l'output format
 * 40015: Si è tentato di impostare a true output group con output format di tipo immagine
 * 40016: Si è tentato di impostare un input type  non valido
 * 40017: Si è tentato di salvare una collection senza aver settato output format
 * 40018: Si è invocato il gateway senza endpoint in GET
 * 40019: Si è tentato di aggiungere un file ad una collection ancora non salvata
 */
class OpenApiUploaderException extends OpenApiExceptionBase
{
  public function __construct($message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}