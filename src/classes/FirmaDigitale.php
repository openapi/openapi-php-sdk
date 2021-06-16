<?php
namespace OpenApi\classes;
class FirmaDigitale extends OpenApiBase {

  /**
   * @param string $token   Il token da utilizzare per il collegamento
   * @param array $scopes   Array con la lista degli scope per cui il token è abilitato
   * @param object $cache   Classe che gestisce la cahce, deve essere una classe che estende {@see OpenApi\clasess\utility\DummyCache} o comunque compatibile con essa (stessi metodi)
   */
  function __construct(string $token,  array $scopes, object $cache, string $prefix){
    parent::__construct($token,  $scopes, $cache, $prefix);
    $this->basePath = "https://ws.firmadigitale.com";
  }


  function getModule($id, $add_header = TRUE){

    $pdf = $this->connect("richiesta/$id/modulo", "GET", [], 0, FALSE, TRUE);
    if(!$add_header){
      return $pdf;
    }
    header("Content-type:application/pdf");
    header("Content-Disposition:attachment;filename={$id}.pdf");
    header('Content-Length: '.strlen( $pdf ));
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Pragma: public');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    echo $pdf;
  }

  
  function requestProduct($data){
    $type = isset($data['tipo'])?$data['tipo']:NULL;
    $codice_prodotto = isset($data['codice_prodotto'])?$data['codice_prodotto']:NULL;
    $anagrafica = isset($data['anagrafica'])?$data['anagrafica']:NULL;
    $spedizione = isset($data['spedizione'])?$data['spedizione']:NULL;
    $urgenza = isset($data['urgenza'])?$data['urgenza']:NULL;
    $assistenza = isset($data['assistenza'])?$data['assistenza']:NULL;
    $callback = isset($data['callback'])?$data['callback']:NULL;
    $quantita = isset($data['quantita'])?$data['quantita']:NULL;

    $params = [];
    if($anagrafica != NULL){
      if($type == "lettore" || $type == "vergine"){
        $params['anagrafica_spedizione'] = $anagrafica;
      }elseif($type == "spid"){
        $params['email'] = $anagrafica->email;
        $params['cellulare'] = $anagrafica->cellulare;
      }else{
        $params['anagrafica'] = $anagrafica;
      }
      
    }

    if($quantita != NULL && ($type == "lettore" || $type == "vergine")){
      $params['quantita'] = $quantita;
    }


    if($spedizione != NULL && ($type == "lettore" || $type == "firma")){
      $params['spedizione'] = $spedizione;
    }

    if($urgenza != NULL && ($type == "lettore" || $type == "firma")){
      $params['urgenza'] = $urgenza;
    }
    if($assistenza != NULL){
      $params['assistenza'] = $assistenza;
    }

    if($callback != NULL){
      $params['callback'] = $callback;
    }
    if(isset($data['options'])){
      foreach($data['options'] as $key =>$value){
        $params[$key] = $value;
      }
    }
//var_dump(json_encode($params));exit;
   
    $ret = $this->connect("richiesta/$codice_prodotto","POST",$params);
    return $ret;
  }

  function addVideoRiconoscimento($id_fd){
    $param['id'] = $id_fd;
    $ret = $this->connect("richiesta/VIDEORIC","POST",$param);
    return $ret;
  }

}