<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";
include_once "ResponseCode.php";

class Dispatcher extends Object
{
    protected $router   = null;
    
    function __construct( Router $router ) 
    {
        $this->router    = $router;
    }

    public function run( $method = null, $uri = null )
    {
        Debug::traceEnterFunc();
        
        // Define which method we need to handle
        $method = is_null($method) ? $this->router->getRequestMethod() : $method;
        Debug::debug("Method: %s", $method );
 
        // The current page URL
        $uri = is_null($uri) ? $this->router->getCurrentUri() : $uri;
        Debug::debug("URI: %s", $uri );

        // Handle the request
        $responseCode = $this->handle($method, $uri );
        
        // Close of the request
        $this->router->closeRequestMethod();
        
        Debug::traceLeaveFunc($responseCode);
        return $responseCode;
    }
    
    public function runRest( )
    {
        Debug::traceEnterFunc();
        
        // Default Response
        $responseCode = new ResponseCode( 404 );    

        // Define which method we need to handle
        $method = is_null($method) ? $this->router->getRequestMethod() : $method;
        Debug::debug("Method: %s", $method );
 
        // Only process the allowed verbs
        if ( in_array( $method, array('GET', 'POST', 'PUT', 'DELETE', 'PATCH') ) )
        {
            // The current page URL
            $uri = is_null($uri) ? $this->router->getCurrentUri() : $uri;
            Debug::debug("URI: %s", $uri );
    
            // Handle the request
            if ( 0 /*$this->handle($method, $uri )*/ )
            {
                $responseCode = new ResponseCode( 200 );    
            }
        }
        else
        {
            $responseCode = new ResponseCode( 400, "Unsupported Verb [$method]" );    
        }
        // Close of the request
        $this->router->closeRequestMethod();

        Debug::traceLeaveFunc($responseCode);
        return $responseCode;
    }
    
    public function handle($method, $uri )
    {
        Debug::traceEnterFunc();
        
        $responseCode = true;
        if ( $this->router->before()->hasRoutes() )
        {
            Debug::debug("Handling (before):" );

            // BEFORE : Call all handlers (blockers/massagers/modifiers/etc...)
            $matches      = $this->router->before()->match( $method, $uri );
            $responseCode = $this->_handle($matches, $method, $uri);
        }
        
        if ( $responseCode && $this->router->route()->hasRoutes())
        {
            Debug::debug("Handling (route):" );
            
            // ROUTES : Call first matched handler only (handlers)
            $matches = $this->router->route()->match( $method, $uri, false );
            if ( count($matches) )
            {
                $responseCode = $this->_handle($matches, $method, $uri );
                if ( $responseCode )
                {
                    // AFTER : Call all handlers (post processing)
                    
                    // We process 'afters' but currently ignore any reponse as the 'deed' has already been done
                    if ( $this->router->after()->hasRoutes() )
                    {
                        Debug::debug("Handling (after):" );
                    
                        $matches = $this->router->after()->match( $method, $uri );
                        $this->_handle($matches, $method, $uri) ;
                    }
                }
            }
        }
        Debug::traceLeaveFunc($responseCode);
        return $responseCode;
    }

    public function _handle($matches, $method, $uri, $object=null)
    {
        $response = true;
        foreach ( $matches as $match )
        {
            Debug::debug("Handling: %s", $match );
            
            $fn       = isset( $match['fn'] )     ? $match['fn']      : 'unknown';
            $params   = isset( $match['params'] ) ? $match['params']  : [];
            $response = $this->call($fn, $params);
            if ( !$response )
            {
                // Keep going until we run out of matches or one of them returns a non-true value
                break;
            }
        }
        return $response;   
    }

    protected function call( $fn, $params )
    {
        $handler = $this->makeHandler($fn);
        if ( is_callable($handler) )
        {
            if ( ! is_array($handler) )
                Debug::debug( 'Dispatching to: %s(%s)', $handler, implode( ',', $params ) );
            else
                Debug::debug( 'Dispatching to: %s@%s(%s)', "".$handler[0], $handler[1], implode( ',', $params ) );
            return call_user_func_array($handler, $params );
        }
        return false;
    }

    protected function makeHandler($fn)
    {
        // Check for class@method type path
        $handler = $fn;
        if ( is_string($fn) )
        {
            $class  = null;
            if ( strpos($fn, '@') )
            {
                list($class, $method) = explode('@', $fn); 
                $method  = ltrim($method, '_');
                $handler = array($class, $method );
            }
        }
        return $handler;        
    }
}

