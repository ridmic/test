<?php
namespace Ridmic\Core;

include_once "core/debug.php";
include_once "core/object.php";

class Request extends Object 
{
    const   PARAM_TOKEN     = '_token';
    const   PARAM_METHOD    = '_method';
    
    protected $method;
    protected $path;
    protected $args;
    protected $notFoundCallback = null;

    function __construct($method = 'GET', $path = '', $args=[] ) 
    {
        $this->setMethod($method) ;
        $this->setPath($path);
        $this->setArgs($args);
    }

    // Setters return $this so we can chain them 
    public function setMethod($v)           { $this->method = strtoupper($v); return $this; }
    public function setPath($v)             { $this->path = $v; return $this; }
    public function setArgs(array $a)       { $this->args = $a; return $this; }
    public function setNotFound($a)         { $this->notFoundCallback = is_callable($a) ? $a : null; return $this; }

    // Getters
    function getMethod()                    { return $this->method; }
    function getPath()                      { return $this->path; }
    function getArgs()                      { return $this->args; }
   
    // Access
    function arg( $name )                   { return array_key_exists($name, $this->args ) ? $this->args[$name] : null; }
    
    function getRequestHeaders()
    {
        Debug::traceEnterFunc();
        
        // Use getallheaders if we have it
        $headers = [];
        if ( function_exists('getallheaders'))
        {
            $headers = getallheaders();
        }
        else
        {
            // Else fake it
            foreach ( $_SERVER as $name => $value )
            {
                if ( (substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH') )
                {
                    $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], strtolower(str_replace('-',' ',substr($name, 5))) )] = $value;
                }
            }
        }
        Debug::traceLeaveFunc($headers);
        return $headers;
    }
    
    function getRequestMethod()
    {
        Debug::traceEnterFunc();
        
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if ( $method == 'HEAD' )
        {
            ob_start();
            $method = 'GET'; // As per HTTP Spec
        }
        elseif ( $method == 'POST' ) 
        {
            $headers = $this->getRequestHeaders();
            if ( isset($headers['X-HTTP-Method-Override']) && in_array( $headers['X-HTTP-Method-Override'], ['PUT','DELETE','PATCH']))
                $method = $headers['X-HTTP-Method-Override'];
        }
        Debug::traceLeaveFunc($method);
        return $method;
    }
    
    function getCurrentURI()
    {
        Debug::traceEnterFunc();
        
        $uri = $_SERVER['REQUEST_URI'];
        if ( strstr( $uri, '?'))
            $uri = substr($uri, 0, strpos($uri, '?'));
        $uri = '/'.trim($uri, '/');

        Debug::traceLeaveFunc($uri);
        return $method;
    }
    
    function getBasePath()
    {
        Debug::traceEnterFunc();
        
        $path = implode( '/', array_slice( explode( '/', $_SERVER['SCRIPT_NAME']), 0, -1 )).'/';
        Debug::traceLeaveFunc($path);
        return $path;
    }

    function run( Dispatcher $dispatcher, Router $router, $always = false )
    {
        Debug::traceEnterFunc();
        
        // Get our method
        $this->method = strtoupper($this->getRequestMethod());
        
        // Do we have a match?
        $response       = $router->matchRoute( $this->method, $this->path );
        $matched        = $dispatcher->setResponse( $response );
        $handled        = 0;
        $okToContinue   = true;

        // Dispatch (multiple) befores (if we have matched our main route)
        if ( $always || $matched )
        {
            // Get our list of matches
            $matches = $router->matchBefore( $this->method, $this->path, true );
            foreach ( $matches as $match )
            {
                if ( $okToContinue && is_array($match) && $dispatcher->setResponse( $match ) )
                {
                    $okToContinue = $dispatcher->dispatch() === true;
                    $handled++;
                }
            }
        }
        
        // Are we ok to continue with the main route?
        if ( $okToContinue )
        {
            // Dispatch routes
            if ( $matched && $dispatcher->setResponse( $response ) )
            {
                $dispatcher->dispatch();
                $handled++;
            }
            
            // Dispatch (multiple) afters (if we have matched our main route)
            if ( $always || $matched )
            {
                // Get our list of matches
                $matches = $router->matchAfter( $this->method, $this->path, true );
                foreach ( $matches as $match )
                {
                    if ( $okToContinue && is_array($match) && $dispatcher->setResponse( $match ) )
                    {
                        $okToContinue = $dispatcher->dispatch() === true;
                        $handled++;
                    }
                }
            }
        }  
        // If we did not find a handler, look for a default
        if ( $handled === 0 )
        {
            if ( is_callable($this->notFoundCallback))
            {
                // Call it
                call_user_func($this->notFoundCallback );
            }
            else
            {
                // 404!
                header($_SERVER['SERVER_PROTOCOL'], '404 Not Found');
            }
        }
        
        // If we were called with a HEAD, then clean up
        if ( $_SERVER['REQUEST_METHOD'] == 'HEAD')
            ob_end_clean();
        
        Debug::traceLeaveFunc($handled);
        return $handled;
    }
    
}

?>