<?php
namespace Ridmic\Core;

require_once "Utils/Config.php";
require_once "Router.php";
require_once "Dispatcher.php";

class App extends Object
{
    protected $name                 = 'unknown';
    protected $version              = '';
    protected $rootPath             = '';
    protected $rootURL              = '';
    protected $language             = 'en';
    protected $appConfig            = null;
    protected $testing              = false;

    protected $router               = null;
    protected $dispatcher           = null;

    public function __construct( $name )
    {
        parent::__construct();
        
        $this->setName( self::toClassName($name) );
    }
    
    // Call this once you have set up the app to initialise it
    public function init( $config = 'Application' )
    {
        // Pull in our config
        $this->appConfig = new Utils\Config;
        $this->appConfig->loadConfig( $this->pathToConfig( $config ) );

        //set timezone
        $timezone = $this->appConfig->get('timezone', 'Europe/London' );
        date_default_timezone_set($timezone);
        
        // Defaults
        $this->language     = $this->appConfig->get('language', $this->language );
        $this->testing      = $this->appConfig->get('testing', $this->testing );

        $this->setRootUrl( $this->appConfig->get('testing', $this->testing ) );        
    }    
    

    public function appRoot()                               { return $this->makePath( array( $this->rootPath, $this->nameAsPath(), 'App') ); }
    public function urlRoot()                               { return $this->rootURL; }
    public function pathRoot()                              { return $this->rootPath; }

    public function pathToApp( $name, $ext='.php' )         { return $this->makePath( array( $this->appRoot(), $name.$ext) ); }
    public function pathToConfig( $name, $ext='.ini' )      { return $this->makePath( array( $this->rootPath,  $this->nameAsPath(), 'Config',   $name.$ext) ); }
    public function pathToLanguage( $name, $ext='.php' )    { return $this->makePath( array( $this->rootPath,  $this->nameAsPath(), 'Language', $this->language, $name.$ext) ); }
    public function pathToPublic( $name, $ext='.php' )      { return $this->makePath( array( $this->rootPath,  $this->nameAsPath(), 'Public',   $name.$ext) ); }
    
    public function isTesting()                             { return $this->testing; }

    public function setName( $name )                        { $this->name = $name; return $this; }
    public function name()                                  { return $this->name; }
    public function setVersion( $version )                  { $this->version = $version; return $version; }
    public function version()                               { return $this->version; }
    public function isVersioned()                           { return $this->version != ''; }
    public function nameAsPath()                            { return trim( implode( '/', [$this->name, $this->version]), '/' ); }
    public function nameAsUrl()                             { return trim( implode( '\\', [$this->name, $this->version]), '\\' ); }
 
    public function setLanguage( $code )                    { $this->language = $code; return $this; }
    public function language()                              { return $this->language; }

    public function makePath( $bits )
    {
        return implode( '/', $bits );
    }
    public function setRootPath( $path )
    {
        $this->rootPath  = rtrim( $path, '/' );
    }

    public function setRootUrl( $url )
    {
        $this->rootURL = rtrim( $url, '/' );
    }
    
    public function setRouter( Router $r )                  { $this->router = $r; return $this; }
    public function router()                                { return $this->router; }
    
    public function setDispatcher( Dispatcher $d )          { $this->dispatcher = $d; return $this; }
    public function dispatcher()                            { return $this->dispatcher; }
    
 
    public function run()
    {
        return $this->dispatcher()->run();
    }

    public function runRest()
    {
        return $this->dispatcher()->runRest();
    }

    // Helpers
    public function config( $index, $default=null )
    {
        if ( !is_null($this->appConfig) )
            return $this->appConfig->get($index, $default);
        return $default;
    }
    
    function getRemoteIP( $allowProxies = false )           
    {
        // Remember - the only 'trusted' IP address is that in 'REMOTE_ADDR' ALL others can be faked!
        $ipaddress = Utils\Input::server('REMOTE_ADDR');
        
        // If you allwo proxies DO NOT trust the IP - use it wisely!
        if ( $allowProxies )
        {
            if ( Utils\Input::hasServer('HTTP_CLIENT_IP') && Utils\Input::server('HTTP_CLIENT_IP') != '127.0.0.1')
                $ipaddress = Utils\Input::server('HTTP_CLIENT_IP');
            else if ( Utils\Input::hasServer('HTTP_X_FORWARDED_FOR') && Utils\Input::server('HTTP_X_FORWARDED_FOR') != '127.0.0.1')
                $ipaddress =Utils\Input::server('HTTP_X_FORWARDED_FOR');
            else if (Utils\Input::hasServer('HTTP_X_FORWARDED') && Utils\Input::server('HTTP_X_FORWARDED') != '127.0.0.1')
                $ipaddress = Utils\Input::server('HTTP_X_FORWARDED');
            else if ( Utils\Input::hasServer('HTTP_FORWARDED_FOR') && Utils\Input::server('HTTP_FORWARDED_FOR') != '127.0.0.1')
                $ipaddress =Utils\Input::server('HTTP_FORWARDED_FOR');
            else if ( Utils\Input::hasServer('HTTP_FORWARDED') && Utils\Input::server('HTTP_FORWARDED') != '127.0.0.1')
                $ipaddress = Utils\Input::server('HTTP_FORWARDED');
        }
        return  Utils\Input::validIP($ipaddress);
    }
    
}

class MvcApp extends App
{
    // Helpers
    public function pathToView( $name, $ext='.php' )        { return $this->makePath( array( $this->appRoot(), 'Views', $name.$ext) ); }
    public function pathToController( $name, $ext='.php' )  { return $this->makePath( array( $this->appRoot(), 'Controllers', self::toClassName($name).$ext) ); }
    public function pathToModel( $name, $ext='.php' )       { return $this->makePath( array( $this->appRoot(), 'Models', $name.$ext) ); }

    public function classOfController( $name )              { return "\\Ridmic\\".$this->name()."\\".self::toClassName($name).'Controller'; }

    public function loadAltController( $controller )
    {
        $file = $this->pathToController( $controller );

        Debug::debug("ALT CONTROLLER (FILE): %s", $file );
        if ( file_exists($file))
        {
            require_once $file;
            $class      = $this->classOfController($controller);
            Debug::debug("ALT CONTROLLER (CLASS): %s", "".$class );
            if ( class_exists($class) )
            {
                Debug::debug("ALT CONTROLLER (CREATED)" );

                $controller = new $class( $this, self::toClassName($controller) );
            }
        }
    }
    
}

class AppFactory extends Object
{
    public static function rootPath()   { return __DIR__.'/../'; }
    
    public static function build( $name, $config = 'Application' )
    {
        Debug::traceEnterFunc();
        
        $app = new App( $name );
        $app->setRootPath(self::rootPath());
        
        $app->init( $config );
        $app->setRouter( new Router() );
        $app->setDispatcher( new Dispatcher( $app->router() ) );
        
        Debug::traceLeaveFunc($app);
        return $app;
    }

    public static function buildMvc( $name, $versioned = false, $config = 'Application' )
    {
        Debug::traceEnterFunc();
        
        $app = new MvcApp( $name );
        $app->setRootPath(self::rootPath());

        $app->init( $config );
        $app->setRouter( new Router() );
        $app->setDispatcher( new Dispatcher( $app->router() ) );
        
        $uri        = $app->router()->getCurrentUri();
        Debug::debug("URI: %s", $uri );
        $bits       = explode( '/', trim($uri, '/') );
        $controller = 'unknown';
        if ( count($bits) )
        {
            $controller = array_shift($bits);
            $app->setVersion( $versioned ? array_shift($bits) : '' );
            $file       = $app->pathToController( $controller );
            Debug::debug("CONTROLLER (FILE): %s", $file );
            if ( file_exists($file))
            {
                require_once $file;
                $class      = $app->classOfController($controller);
                Debug::debug("CONTROLLER (CLASS): %s", "".$class );
                if ( class_exists($class) )
                {
                    Debug::debug("CONTROLLER (CREATED)" );

                    $baseRoute = rtrim( implode( '/', [$controller, $app->version()] ), '/');
                    Debug::debug( "Base Route: %s",$baseRoute );
                    $app->router()->setBaseRoute( $baseRoute );

                    $controller = new $class( $app, self::toClassName($controller) );
                }
            }
        }
        Debug::traceLeaveFunc($app);
        return $app;
    }
}

