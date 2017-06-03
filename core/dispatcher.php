<?php
namespace Ridmic\Core;

include_once "core/object.php";

class Dispatcher extends Object
{
  protected $controller = null;
  protected $method     = null;
  protected $args       = [];
  
  function __construct( $c = null, $m = null, $a = [] )
  {
    $this->controller = $c;
    $this->method     = $m;
    $this->args       = $a;
  }  

  public function dispatch( )
  {
     $handler = is_null($this->controller) ? $this->method : array( $this->controller, $this->method);

    // Call the handler?
    if ( is_callable($handler) )
    {
       return call_user_func_array($handler, $this->args );
    }
     return null;
  }
}

