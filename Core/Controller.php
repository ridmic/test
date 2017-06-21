<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";
include_once "ResponseCode.php";

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
        return $this->makeResponse( ResponseCode::CODE_OK );
    }
    
    protected function makeResponse( $code, $response )
    {
        return new ResponseCode( $code, $response );
    }
    
    protected function registerRoutes( Router $router )
    {
        $router->route()->add( 'ALL', '{:any}', [$this, 'index' ] );        
    }
}
