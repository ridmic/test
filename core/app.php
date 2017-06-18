<?php
namespace Ridmic\Core;

require_once "utils/config.php";
require_once "router.php";
require_once "dispatcher.php";

class App extends Object
{
    protected $name                 = 'unknown';
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
        $this->setName( $name );
    }
    
   // Call this once you have set up the app to initialise it
    public function init( $config = 'application' )
    {
        // Pull in our config
        $this->appConfig = new Utils\Config;
        $this->appConfig->loadConfig( $this->pathToConfig( $config ) );

        //set timezone
        $timezone = $this->appConfig->get('timezone', 'Europe/London' );
        date_default_timezone_set($timezone);
        
        // Defaults
        $this->language = $this->appConfig->get('language', $this->language );
        $this->testing  = $this->appConfig->get('testing', $this->testing );
        $this->setRootUrl( $this->appConfig->get('testing', $this->testing ) );        
    }    
    

    public function pathToApp( $name, $ext='.php' )         { return $this->makePath( array( $this->rootPath, 'app', $name.$ext) ); }
    public function pathToConfig( $name, $ext='.ini' )      { return $this->makePath( array( $this->rootPath, 'config', $name.$ext) ); }
    public function pathToLanguage( $name, $ext='.php' )    { return $this->makePath( array( $this->rootPath, 'language', $this->language(), $name.$ext) ); }
    public function pathToPublic( $name, $ext='.php' )      { return $this->makePath( array( $this->rootPath, 'public', $name.$ext) ); }
    public function urlRoot( $name )                        { return $this->makePath( array( $this->rootURL, $name )); }
    public function pathRoot( $name )                       { return $this->makePath( array( $this->rootPath, $name )); }

    public function isTesting()                             { return $this->testing; }

    public function setName( $name )                        { $this->name = $name; return $this; }
    public function name()                                  { return $this->name; }
 
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
    public function pathToView( $name, $ext='.php' )        { return $this->makePath( array( $this->rootPath, 'app/views', $name.$ext) ); }
    public function pathToController( $name, $ext='.php' )  { return $this->makePath( array( $this->rootPath, 'app/controllers', $name.$ext) ); }
    public function pathToModel( $name, $ext='.php' )       { return $this->makePath( array( $this->rootPath, 'app/models', $name.$ext) ); }
    public function pathToLanguage( $name, $ext='.php' )    { return $this->makePath( array( $this->rootPath, 'app/language', $name.$ext) ); }

}


class AppFactory extends Object
{
    public static function build( $name )
    {
        Debug::traceEnterFunc();
        
        $app = new App( $name );

        $app->setRouter( new Router() );
        $app->setDispatcher( new Dispatcher( $app->router() ) );
        
        $app->setRootPath(__DIR__.'/../');
        
        Debug::traceLeaveFunc($app);
        return $app;
    }
    
    public static function buildMvc( $name )
    {
        Debug::traceEnterFunc();
        
        $app = new MvcApp( $name );

        $app->setRouter( new Router() );
        $app->setDispatcher( new Dispatcher( $app->router() ) );
        
        $app->setRootPath(__DIR__.'/../');
 
        $uri        = $app->router()->getCurrentUri();
        Debug::debug("URI: %s", $uri );
        $bits       = explode( '/', trim($uri, '/') );
        $controller = 'unknown';
        if ( count($bits) )
        {
            $controller = array_shift($bits);
            $controller = self::toClassName($controller);
            $file       = $app->pathToController( $controller );
            Debug::debug("CONTROLLER (FILE): %s", $file );
            if ( file_exists($file))
            {
                require_once $file;
                $class      = "\\Ridmic\\App\\".self::toClassName($controller).'Controller';
                Debug::debug("CONTROLLER (CLASS): %s", "".$class );
                if ( class_exists($class) )
                {
                    Debug::debug("CONTROLLER (CREATED)" );
                    new $class( $app->router() );
                }
            }
        }
        Debug::traceLeaveFunc($app);
        return $app;
    }

}

