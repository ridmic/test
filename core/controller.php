<?php
namespace Ridmic\Core;

include_once "core/object.php";

class Controller extends Object
{
  protected $routes = [];

  public function __construct( Router $router )
  {
      parent::__construct();
      $this->registerRoutes( $router );
  }
  
  
  protected function registerRoutes( Router $router )
  {
    foreach ( $this->routes as $method => $route )
    {
      foreach ( $route as $path => $handler )
      {
        $router->addRoute( $method, $path, $handler );        
      }
    }
  }
    
}


?>