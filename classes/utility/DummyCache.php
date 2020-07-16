<?php
namespace OpenApi\classes\utility;
class DummyCache {
  function get(string $key){
    return false;
  }
  function save(string $key ,   $value,  $ttl = 300){
    return $value;
  }

  function delete(string $key){
    return true;
  }

  function clean(){
    return true;
  }

}