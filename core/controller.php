<?php
namespace Ridmic\Core;

include_once "core/debug.php";
include_once "core/object.php";

class Controller extends Object
{
  public function __construct( Router $router )
  {
      parent::__construct();
      $this->registerRoutes( $router );
  }
  
  public function index()
  {
    Debug::write('Hello World!');
  }


  protected function registerRoutes( Router $router )
  {
    $router->addRoute( 'any', '{:any}', [$this, 'index' ] );        
  }
    
}


?>