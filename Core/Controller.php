<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";
include_once "ResponseCode.php";

class Controller extends Object
{
    protected $router = null;
    
    public function __construct( Router $router )
    {
        parent::__construct();
        
        $this->router = $router;
        $this->registerRoutes();
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
    
    protected function registerRoutes()
    {
        $this->addRoute( 'ALL', '{:any}', [$this, 'index' ] );        
    }
    
    protected function addBefore( $m, $p, $c )  { $this->router->before()->add( $m, $p, $c ); }
    protected function addRoute( $m, $p, $c )   { $this->router->route()->add( $m, $p, $c ); }
    protected function addAfter( $m, $p, $c )   { $this->router->after()->add( $m, $p, $c ); }
}
