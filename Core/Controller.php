<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";

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
        return true;
    }
    
    protected function registerRoutes( Router $router )
    {
        $router->route()->add( 'ALL', '{:any}', [$this, 'index' ] );        
    }
}
