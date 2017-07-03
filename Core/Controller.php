<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";
include_once "ResponseCode.php";

class Controller extends Object
{
    protected $app      = null;
    protected $router   = null;   
    protected $view     = null;
    protected $name     = '-unknown-';
    
    public function __construct( App $app )
    {
        parent::__construct();
    
        $this->app      = $app;
        $this->router   = $app->router();
        $this->view     = new View( $app );

        // Register our routes
        $this->registerRoutes();
    }
    
    public function index()
    {
        Debug::write('Hello World!');
        return $this->makeResponse( ResponseCode::CODE_OK );
    }
    
    protected function makeResponse( $code, $response = null )
    {
        return new ResponseCode( $code, $response );
    }
    
    public function block()
    {
        return $this->makeResponse( ResponseCode::CODE_FORBIDDEN );
    }
    
    protected function registerRoutes()
    {
        $this->addRoute( 'ALL', '{:any}', [$this, 'index' ] );        
    }
    
    protected function addBefore( $m, $p, $c )  { $this->router->before()->add( $m, $p, $c ); }
    protected function addRoute( $m, $p, $c )   { $this->router->route()->add( $m, $p, $c ); }
    protected function addAfter( $m, $p, $c )   { $this->router->after()->add( $m, $p, $c ); }
}
