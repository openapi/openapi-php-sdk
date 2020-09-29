<?php
namespace OpenApi\classes\utility;
if (session_status() == PHP_SESSION_NONE) {session_start();}
class sessionStoreToken implements storeTokenInterface{
  function get(){
    
    return isset($_SESSION['openapi'])?$_SESSION['openapi']:NULL;
  }
  function save($data){
    $_SESSION['openapi'] = $data;;
  }
  function clear(){
    unset($_SESSION['openapi']);
  }

  function isset(){
    return isset($_SESSION['openapi']);
  }
}