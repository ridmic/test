<?php
namespace Ridmic\Core;

include_once "core/debug.php";
include_once "core/object.php";

class Dispatcher extends Object
{
  protected $class  = null;
  protected $method = null;
  protected $args   = [];
  
  function __construct( $c = null, $m = null, $a = [] )
  {
    $this->class  = $c;
    $this->method = $m;
    $this->args   = $a;
  }  

  public function setResponse( $resp )
  {
    Debug::traceEnterFunc();
  
    $this->class  = isset($resp['class']) ? $resp['class'] : null;
    $this->method = isset($resp['method']) ? $resp['method'] : null;
    $this->args   = isset($resp['args']) ? $resp['args'] : [];

    $handler = is_null( $this->class ) ? $this->method : array( $this->class, $this->method );
    $retVal  = is_callable($handler);

    Debug::traceLeaveFunc($retVal);
    return $retVal;
  }
  
  public function dispatch( $c = null, $m = null, $a = null )
  {
    Debug::traceEnterFunc();

    $retVal = null;
    $class  = is_null($c) ? $this->class : $c;
    $method = is_null($m) ? $this->method : $m;
    $args   = is_null($a) ? $this->args : $a;
  
    $handler = is_null( $class ) ? $method : array( $class, $method );

    // Call the handler?
    if ( is_callable($handler) )
    {
       if ( is_null($class) )
         Debug::debug( 'Dispatching to: %s(%s)', $method, implode( ',', $args ) );
       else
         Debug::debug( 'Dispatching to: %s@%s(%s)', "".$class, $method, implode( ',', $args ) );
       $retVal = call_user_func_array($handler, $args );
    }
    else
    {
       Debug::debug( 'Nothing to dispatch to!' );
    }
    Debug::traceLeaveFunc($retVal);
    return $retVal;
  }
}

