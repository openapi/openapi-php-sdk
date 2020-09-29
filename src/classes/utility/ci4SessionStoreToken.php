<?php
namespace OpenApi\classes\utility;
class ci4SessionStoreToken implements storeTokenInterface{
  function get(){
    return session("openapi");
  }
  function save($data){
    session()->set("openapi",$data);
  }

  function clear(){
    session()->remove("openapi");
  }

  function isset(){
    return session()->has("openapi");
  }
}