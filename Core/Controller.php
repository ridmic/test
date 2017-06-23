<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";
include_once "ResponseCode.php";

class Controller extends Object
{
    protected $app      = null;
    protected $router   = null;        
    
    public function __construct( App $app )
    {
        parent::__construct();
    
        $this->app      = $app;
        $this->router   = $app->router();

        // Set up our base path
        $baseRoute = rtrim( implode( '/', [self::toURLName($this->app->name()), $this->app->version()] ), '/');
        Debug::debug( "Base Route: %s",$baseRoute );
        $this->router->setBaseRoute( $baseRoute );
        
        // Register our routes
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
