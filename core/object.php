<?php
namespace Ridmic\Core;

include_once "core/debug.php";

class Object
{
    function __construct()
    {
    }
    
    function __destruct()
    {
    }
    
    public function __toString()
    {
      return "Object(".get_class($this).")";
    }
}
