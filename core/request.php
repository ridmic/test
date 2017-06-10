<?php
namespace Ridmic\Core;

include_once "core/debug.php";
include_once "core/object.php";

class Request extends Object 
{
    const   PARAM_TOKEN     = '_token';
    const   PARAM_METHOD    = '_method';
    
    protected $method;
    protected $path;
    protected $args;
 
    function __construct($method = 'get', $path = '', $args=[] ) 
    {
        $this->setMethod($method) ;
        $this->setPath($path);
        $this->setArgs($args);
    }

    // Setters return $this so we can chain them 
    public function setMethod($v)           { $this->method = $v; return $this; }
    public function setPath($v)             { $this->path = $v; return $this; }
    public function setArgs(array $a)       { $this->args = $a; return $this; }

    // Getters
    function getMethod()                    { return $this->method; }
    function getPath()                      { return $this->path; }
    function getArgs()                      { return $this->args; }
   
    // Access
    function arg( $name )                   { return array_key_exists($name, $this->args ) ? $this->args[$name] : null; }
    
    
    function run( Dispatcher $dispatcher, Router $router )
    {
      // Try and dispatch befores
      if ( $dispatcher->setResponse( $router->matchBefore( $this->method, $this->path ) ) )
        $dispatcher->dispatch();
    
      // Try and dispatch routes
      if ( $dispatcher->setResponse( $router->matchRoute( $this->method, $this->path ) ) )
        $dispatcher->dispatch();
    
        // Try and dispatch afters
      if ( $dispatcher->setResponse( $router->matchAfter( $this->method, $this->path ) ) )
        $dispatcher->dispatch();
    }
    
    
}

?>