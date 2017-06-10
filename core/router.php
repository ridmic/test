<?php
namespace Ridmic\Core;

include_once "core/debug.php";
include_once "core/object.php";

class Router extends Object {

    const REGVAL = '#({:.+?})#';    
    
    protected $before = ['any' => [], 'get' => [], 'post' => [], 'put' => [], 'delete' => [], 'options' => [], 'patch' => [], 'head' => [] ];
    protected $routes = ['any' => [], 'get' => [], 'post' => [], 'put' => [], 'delete' => [], 'options' => [], 'patch' => [], 'head' => [] ];
    protected $after  = ['any' => [], 'get' => [], 'post' => [], 'put' => [], 'delete' => [], 'options' => [], 'patch' => [], 'head' => [] ];

    protected $patterns = [
        ':any'      => '.*',
        ':id'       => '[0-9]+',
        ':slug'     => '[a-z\-]+',
        ':name'     => '[a-zA-Z]+',
        ':alphanum' => '[0-9a-zA-Z]+'
    ];
    
    protected   $namedRoutes    = [];
    protected   $baseURL        = "";
    protected   $exactMatch     = true;
    
    function __construct( $exactMatch = true ) 
    {
        $this->exactMatch = $exactMatch;
    }

    public function allowedMethods()
    {
        return array_keys( $this->routes );
    }

    public function addBefore($method, $pattern, $handler , $name = '')
    {
        $this->addRouteType('before', $method, $pattern, $handler , $name = '');
    }
    
    public function addRoute($method, $pattern, $handler , $name = '')
    {
        $this->addRouteType('route', $method, $pattern, $handler , $name = '');
    }

    public function addAfter($method, $pattern, $handler , $name = '')
    {
        $this->addRouteType('after', $method, $pattern, $handler , $name = '');
    }
    
    public function addRouteType($type, $method, $pattern, $handler , $name = '')
    {
        Debug::traceEnterFunc();

        switch( $type )
        {
            case 'before':
                $this->before[$method][$pattern] = [$name => $handler];
                break;
                
            case 'after':
                $this->after[$method][$pattern] = [$name => $handler];
                break;
                
            default:
                $this->routes[$method][$pattern] = [$name => $handler];
                if ( is_string($name) && $name != '' )
                {
                    // Check for a regex type path
                    if ( preg_match(self::REGVAL, $pattern, $matches) )
                    {
                        $matches = preg_split(self::REGVAL, $pattern);
                        $pattern = rtrim($matches[0], '/');
                    }
                    $this->namedRoutes[$name] = $pattern;
                }
                break;
        }
        // Bit of debugging
        if ( is_array($handler) )
          Debug::debug( "Routing (%s): %s:%s to '%s' => '%s@%s'", $type, $method, $pattern, $name, "".$handler[0], "".$handler[1] );
        else
          Debug::debug( "Routing (%s): %s:%s to '%s' => '%s'", $type, $method, $pattern, $name, $handler );
          
        Debug::traceLeaveFunc();
    }

    public function matchBefore( $m, $request )
    {
        Debug::traceEnterFunc();
        
        $retVal = $this->_match( $this->before, $m, $request );
        
        Debug::traceLeaveFunc( $retVal );
        return $retVal;
    }

    public function matchRoute( $m, $request )
    {
        Debug::traceEnterFunc();
        
        $retVal = $this->_match( $this->routes, $m, $request );
        
        Debug::traceLeaveFunc( $retVal );
        return $retVal;
    }

    public function matchAfter( $m, $request )
    {
        Debug::traceEnterFunc();
        
        $retVal = $this->_match( $this->after, $m, $request );
        
        Debug::traceLeaveFunc( $retVal );
        return $retVal;
    }


    protected function _match( $routes, $m, $request )
    {
        Debug::traceEnterFunc();
        
        $retVal  = [];
        $methods = array( $m, 'any' );
        Debug::debug("Matching: %s:%s", $method, $request);
        foreach ( $methods as $method )
        {
            foreach ($routes[$method] as $pattern => $handler) 
            {
                Debug::debug("Against: %s:%s", $method, $pattern );
                $args   = []; 
                $class  = null;
                list($name, $meth) = each($handler); 

                // Check for a regex type path
                if ( preg_match(self::REGVAL, $pattern) )
                {
                    list($args, $uri, $pattern) = $this->parseRegexRoute($request, $pattern); 
                    Debug::debug("Expanding to: %s:%s", $method, $pattern );
                }
    
                // Do we have a match?
                if ( !preg_match(($this->exactMatch ? "#^$pattern$#" : "#^$pattern#"), $request) )
                    continue ;

                Debug::debug("Matched: $request");

                // Check for class@method type path
                if ( is_string($meth) && strpos($meth, '@'))
                {
                    list($class, $meth) = explode('@', $meth); 
                }
                // Check for object
                if ( is_array($meth) && is_object($meth[0]) )
                {
                    $class = $meth[0];
                    $meth  = $meth[1]; 
                }
                $retVal = ["class" => $class, "method" => $meth, "args" => $this->cleanInputs($args) ];
                Debug::traceLeaveFunc( $retVal );
                return $retVal;
            }
        }
        Debug::traceLeaveFunc( $retVal );
        return $retVal;
    }

    public function routeTo( $name )
    {
        $route = '/';
        if ( array_key_exists($name, $this->namedRoutes) )
            $route =  $this->baseURL . $this->namedRoutes[$name];
         return $route;
    }
    
    public function setBaseURL( $url )
    {
        if (is_string($url) )
            $this->baseURL = rtrim($url,'/');
    }
    public function baseURL()  { return $this->baseURL;   }
    
    public function routes()
    {
        return $this->routes;
    }

    // =========================================================================    
    
    protected function parseRegexRoute($requestUri, $resource)
    {
        $route = preg_replace_callback(self::REGVAL, function($matches) 
                                                     {
                                                        $patterns   = $this->patterns; 
                                                        $matches[0] = str_replace(['{', '}'], '', $matches[0]);
                                                        if ( in_array($matches[0], array_keys($patterns)) )
                                                        {                       
                                                            return  $patterns[$matches[0]];
                                                        }
                                                        return ltrim($matches[0], ':');
                                                     }, $resource );

        $regUri = explode('/', $resource); 
        $args   = array_diff( array_replace($regUri, explode('/', $requestUri)), $regUri );  
        return [array_values($args), $resource, $route]; 
    }
    
    private function cleanInputs( $data ) 
    {
        $clean_input = array();
        if (is_array($data)) 
        {
            foreach ($data as $k => $v) 
            {
                $clean_input[$k] = $this->cleanInputs($v);
            }
        } 
        else 
        {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

}