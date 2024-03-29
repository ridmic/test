<?php namespace DryMile\Core;

include_once "Utils/Object.php";
include_once "Debug.php";
include_once "ResponseCode.php";
include_once "View.php";

class Controller extends Utils\ObjectX
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
    
    public function makeFormNonce()
    {
        //generate new nonce for form
        $nonce = Utils\Secure::generateToken(32);
        
        // Push it into our session
        if ( $this->app->usingSessions() )
            $this->app->session()->set( "csrf", $nonce );
                
        // and return it
        return $nonce;
    }
    
    public function checkFormNonce( $name = 'nonce')
    {
        if ( $this->app->usingSessions() )
        {
            if ( Utils\Input::hasPost( $name ) )
                return Utils\Input::post( $name ) == $this->app->session()->get( "csrf" );
        }
        return false;
    }

    public function checkAntiSpam( $name = 'url')
    {
        if ( Utils\Input::hasPost( $name ) )
            return Utils\Input::post( $name ) == "";
        return false;
    }
    
    public function checkCaptcha( $name = 'captcha')
    {
        if ( $this->app->usingSessions() )
        {
            if ( !is_null( $this->session->get($name) ) )
                return Utils\Input::hasPost( $name ) && Utils\Input::validInt( $name ) && Utils\Input::postInt( $name ) === (int)$this->session->get($name);
        }
        return false;
    }
    
    // Helpers
    protected function L( $index, $args=[] )
    {
        return $this->view->L( $index, $args );
    }
    
    protected function loadPlugin( $name )
    {
        $this->app->loadPlugin( $name );
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
