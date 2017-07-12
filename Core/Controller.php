<?php
namespace DryMile\Core;

include_once "Utils/Object.php";
include_once "Debug.php";
include_once "ResponseCode.php";
include_once "View.php";

class Controller extends Utils\Object
{
    protected $app          = null;
    protected $router       = null;   
    protected $responder    = null;   
    protected $view         = null;
    protected $name         = '-unknown-';
    
    public function __construct( App $app, $name = '-unknown-' )
    {
        parent::__construct();
    
        $this->app          = $app;
        $this->name         = $name;
        $this->router       = $app->router();
        $this->responder    = $app->responder();
        $this->view         = new View( $app );
        
        // Load any default languages
        $this->view->loadLanguage( '_common' );
        $this->view->loadLanguage( $this->name );


        // Give access to our view object
        $this->view->assignObject( 'view', $this->view );
        
        // Register our routes
        $this->registerRoutes();
    }
    public function name()      { return $this->name; }
    
    public function index()
    {
        Debug::write('Hello World!');
        return $this->makeResponse( ResponseCode::CODE_OK );
    }
    
    protected function L( $index, $args=[] )
    {
        return $this->view->L( $index, $args );
    }
    
    protected function makeResponse( $code, $response = null )
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
