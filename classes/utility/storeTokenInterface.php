<?php
namespace OpenApi\classes\utility;
interface  storeTokenInterface {
  function get();
  function save($data);
  function clear();
  function isset();
}