<?php
namespace Openapi\classes\utility;
interface  CacheSystemInterface {
  function get(string $key);
  function save(string $key ,   $value,  $ttl = 300);
  function delete(string $key);
  function clean();
}