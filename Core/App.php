<?php
namespace DryMile\Core;

require_once "Utils/Config.php";
require_once "Router.php";
require_once "Dispatcher.php";
require_once "Utils/Session.php";
require_once "Utils/JwtClaim.php";
require_once "Utils/Logger.php";

class App extends Object
{
    protected $domain               = 'unknown';
    protected $name                 = 'unknown';
    protected $version              = '';
    protected $rootPath             = '';
    protected $rootURL              = '';
    protected $language             = 'en';
    protected $appConfig            = null;
    protected $testing              = false;
    protected $initCount            = 0;

    protected $session              = null;
    protected $useSessions          = false;

    protected $router               = null;
    protected $dispatcher           = null;
    protected $responder            = null;
    
    protected $logger               = null;
    protected $logName              = 'logFile';
    
    protected $debugLevel           = Debug::DBG_WRITE;

    public function __construct( $name )
    {
        parent::__construct();
        
        $this->setName( self::toClassName($name) );
    }
    
    // Call this once you have set up the app to initialise it
    public function init( $full = true, $config = 'Application' )
    {
        // Pull in our config
        $this->appConfig = new Utils\Config;
        $this->appConfig->loadConfig( $this->pathToConfig( $config ) );

        //set timezone
        $timezone = $this->appConfig->get('timezone', 'Europe/London' );
        date_default_timezone_set($timezone);
        
        // Defaults
        $this->domain       = $this->appConfig->get('domain', $this->domain );
        $this->language     = $this->appConfig->get('language', $this->language );
        $this->testing      = $this->appConfig->get('testing', $this->testing );
        $this->debugLevel   = $this->appConfig->get('debuglevel', $this->debugLevel );
        $this->useSessions  = $this->appConfig->get('sessions', $this->useSessions );
        $this->logName      = $this->appConfig->get('logfile', $this->name() );
        $this->logWrap      = $this->appConfig->get('logwrap', Utils\FileLogger::WRAP_DAILY );

        // These should only be called once but can be postponed by setting $full = false
        if ( $full && is_null($this->logger) )
        {
            // Create a log for our debug
            Debug::level( $this->debugLevel );
            Debug::addLogger( new Utils\FileLogger( $this->pathToLogs( $this->name.'-Debug' )) );
            Debug::debug('[CORE VER]: ' . CORE_VER );
    
            // Logger
            $this->logger = new Utils\FileLogger( $this->pathToLogs( $this->logName ), $this->logWrap );

            // Create our session?
            if ( $this->useSessions )
            {
                $this->session = new Utils\Session( $this->name() );
                $this->session->start();
                if ( !$this->session->isValid())
                {
                    $this->session->destroy();
                    $this->session->start();
                }
            }
        }
    }    

    public function appRoot()                               { return $this->makePath( array( $this->rootPath, $this->nameAsPath(), 'App') ); }
    public function urlRoot()                               { return $this->rootURL; }
    public function pathRoot()                              { return $this->rootPath; }

    public function pathToApp( $name, $ext='.php' )         { return $this->makePath( array( $this->appRoot(), $name.$ext) ); }
    public function pathToConfig( $name, $ext='.ini' )      { return $this->makePath( array( $this->rootPath,  $this->nameAsPath(), 'Config',   $name.$ext) ); }
    public function pathToLanguage( $name, $ext='.php' )    { return $this->makePath( array( $this->rootPath,  $this->nameAsPath(), 'Language', $this->language, $name.$ext) ); }
    public function pathToPublic( $name, $ext='.php' )      { return $this->makePath( array( $this->rootPath,  $this->nameAsPath(), 'Public',   $name.$ext) ); }
    public function pathToLogs( $name, $ext='.log' )        { return $this->makePath( array( $this->rootPath,  $this->nameAsPath(), 'Data', 'Logs', $name.$ext) ); }
    public function pathToCache( $name, $ext='' )           { return $this->makePath( array( $this->rootPath,  $this->nameAsPath(), 'Data', 'Cache', $name.$ext) ); }

    public function pathToCoreController($name,$ext='.php') { return $this->makePath( array( $this->rootPath, 'Core', 'Controllers', self::toClassName($name).$ext) ); }
    
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

    public function session()                               { return $this->useSessions ? $this->session : die('Fatal Session Error'); }

    public function logger()                                { return $this->logger; }

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
    
    public function setResponder( Responder $d )            { $this->responder = $d; return $this; }
    public function responder()                             { return $this->responder; }
    
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
    
    public function loadCoreControllerByName( $controller )
    {
        return $this->loadCoreController( self::toURLName($controller) );
    }
    
    public function loadCoreController( $controller )
    {
        $file = $this->pathToCoreController( $controller );

        Debug::debug("CORE CONTROLLER (FILE): %s", $file );
        if ( file_exists($file))
        {
            require_once $file;
            $class      = "\\DryMile\\Core\\Controller\\".self::toClassName($controller).'Controller';
            Debug::debug("CORE CONTROLLER (CLASS): %s", "".$class );
            if ( class_exists($class) )
            {
                Debug::debug("CORE CONTROLLER (CREATED)" );

                return new $class( $this, self::toClassName($controller) );
            }
        }
        return null;
    }
    
    public function makeJwt( $expiresIn = null )
    {
        $jwt = new Utils\JwtClaim();
        $jwt->setIssuer( $this->domain );
        $jwt->setSubject( $this->name );
        $jwt->setAudience( $this->name );
        $jwt->setNotBefore(time());
        $jwt->setExpiration( time()+(is_null($expiresIn) ? 60 : (is_integer($expiresIn) ? $expiresIn : 60)));
        $jwt->setIssued(time());
        $jwt->setIdentifier(Utils\Secure::generateToken());
        
        return $jwt;
    }
}

class MvcApp extends App
{
    // Helpers
    public function pathToView( $name, $ext='.php' )        { return $this->makePath( array( $this->appRoot(), 'Views', $name.$ext) ); }
    public function pathToController( $name, $ext='.php' )  { return $this->makePath( array( $this->appRoot(), 'Controllers', self::toClassName($name).$ext) ); }
    public function pathToModel( $name, $ext='.php' )       { return $this->makePath( array( $this->appRoot(), 'Models', $name.$ext) ); }

    public function classOfController( $name )              { return "\\DryMile\\".$this->name()."\\".self::toClassName($name).'Controller'; }

    public function loadAltControllerByName( $controller )
    {
        return $this->loadAltController( self::toURLName($controller) );
    }
    
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

                return new $class( $this, self::toClassName($controller) );
            }
        }
        return null;
    }
    
}

class AppFactory extends Object
{
    public static function rootPath()   { return __DIR__.'/../'; }
    
    public static function build( $name, $rType = Responder::TYPE_JSON, $config = 'Application' )
    {
        Debug::traceEnterFunc();
        
        $app = new App( $name );
        $app->setRootPath(self::rootPath());
        
        $app->init( true, $config );
        $app->setRouter( new Router() );
        $app->setDispatcher( new Dispatcher( $app->router() ) );
        $app->setResponder( new Responder( $rType ) );
        
        Debug::traceLeaveFunc($app);
        return $app;
    }

    public static function buildMvc( $name, $versioned = false, $rType = Responder::TYPE_JSON, $config = 'Application' )
    {
        Debug::traceEnterFunc();
        
        $app = new MvcApp( $name );
        $app->setRootPath(self::rootPath());
        
        $app->init( false, $config );
        $app->setRouter( new Router() );
        $app->setDispatcher( new Dispatcher( $app->router() ) );
        $app->setResponder( new Responder( $rType ) );
        
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
                // Re-run our init to take note of any versioning
                $app->init( true, $config );
                
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

