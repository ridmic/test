<?php
namespace Ridmic\Core;

include_once "core/debug.php";
include_once "core/object.php";

class Router extends Object {

    const REGVAL = '#({:.+?})#';    
    
    protected $before   = ['ANY' => [], 'GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => [], 'OPTIONS' => [], 'PATCH' => [], 'HEAD' => [] ];
    protected $routes   = ['ANY' => [], 'GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => [], 'OPTIONS' => [], 'PATCH' => [], 'HEAD' => [] ];
    protected $after    = ['ANY' => [], 'GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => [], 'OPTIONS' => [], 'PATCH' => [], 'HEAD' => [] ];

    protected $patterns = [
        ':any'      => '.*',
        ':id'       => '[0-9]+',
        ':slug'     => '[a-z\-]+',
        ':name'     => '[a-zA-Z]+',
        ':alphanum' => '[0-9a-zA-Z]+'
    ];
    
    protected   $namedRoutes        = [];
    protected   $baseRoute          = "";
    protected   $exactMatch         = true;
    protected   $supportedMethods   = ['ANY', 'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'HEAD' ];
    
    function __construct( $exactMatch = true ) 
    {
        $this->exactMatch = $exactMatch;
    }

    public function allowedMethods()
    {
        return array_keys( $this->routes );
    }

    public function addBefore($method, $pattern, $handler )
    {
        $this->route('before', $method, $pattern, $handler );
    }
    
    public function addRoute($method, $pattern, $handler , $name = '')
    {
        $this->route('route', $method, $pattern, $handler , $name );
    }

    public function addAfter($method, $pattern, $handler )
    {
        $this->route('after', $method, $pattern, $handler );
    }
    
    public function matchBefore( $m, $request, $matchAll=false )
    {
        Debug::traceEnterFunc();
        
        Debug::debug( "Matching BEFORE (BEG)" );
        $retVal = $this->match( $this->before, $m, $request, $matchAll );
        Debug::debug( "Matching BEFORE (END)" );
        
        Debug::traceLeaveFunc( $retVal );
        return $retVal;
    }

    public function matchRoute( $m, $request  )
    {
        Debug::traceEnterFunc();
        
        Debug::debug( "Matching ROUTE (BEG)" );
        $retVal = $this->match( $this->routes, $m, $request );
        Debug::debug( "Matching ROUTE (END)" );
        
        Debug::traceLeaveFunc( $retVal );
        return $retVal;
    }

    public function matchAfter( $m, $request, $matchAll=false  )
    {
        Debug::traceEnterFunc();
        
        Debug::debug( "Matching AFTER (BEG)" );
        $retVal = $this->match( $this->after, $m, $request, $matchAll );
        Debug::debug( "Matching AFTER (END)" );
        
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
    
    public function setBaseRoute( $route )
    {
        if (is_string($route) )
            $this->baseRoute = rtrim($route,'/');
    }
    public function baseRoute()  { return $this->baseRoute;   }
    
    public function routes()
    {
        return $this->routes;
    }
    

    // =========================================================================    
    

    protected function route($type, $methods, $pattern, $handler , $name = '')
    {
        Debug::traceEnterFunc();

        // Apply any base route
        $pattern = rtrim($this->baseRoute) . '/' . trim($pattern,'/');

        // Allow mutiple methods
        foreach ( explode('|', strtoupper($methods) ) as $method )
        {
            // Supported method?
            if ( ! in_array( $method, $this->supportedMethods ))
            {
                Debug::debug( "Unsupported Method: %s", $method );
                continue;
            }
            
            switch( $type )
            {
                case 'before':
                    $this->before[$method][] = [$pattern => [$name => $handler]];
                    break;
                    
                case 'after':
                    $this->after[$method][] = [$pattern => [$name => $handler]];
                    break;
                    
                case 'route':
                default:
                    $this->routes[$method][] = [$pattern => [$name => $handler]];
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
        }  
        Debug::traceLeaveFunc();
    }

    // We only match first item!
    protected function match( $routes, $m, $request, $matchAll=false )
    {
        Debug::traceEnterFunc();
        
        $retVal  = [];
        $methods = array( strtoupper($m), 'ANY' );
        Debug::debug("Matching: %s:%s", $method, $request);
        foreach ( $methods as $method )
        {
            foreach ($routes[$method] as $route ) 
            {
                foreach ( $route as $pattern => $handler )
                {
                    Debug::debug("Against: %s:%s", $method, $pattern );
                    $args   = []; 
                    $class  = null;
                    list($name, $meth) = each($handler); 
    
                    $exactMatch = substr($pattern, -1) == '#' ? false : $this->exactMatch;
                    $pattern    = substr($pattern, -1) == '#' ? substr($pattern, 0, -1) : $pattern;

                    // Check for a regex type path
                    if ( preg_match(self::REGVAL, $pattern) )
                    {
                        list($args, $uri, $pattern) = $this->parseRegexRoute($request, $pattern); 
                        Debug::debug("Expanding to: %s:%s", $method, $pattern );
                    }
        
                    // Do we have a match?
                    if ( !preg_match(($exactMatch ? "#^$pattern$#" : "#^$pattern#"), $request) )
                        continue ;
    
                    Debug::debug("Matched: %s", $request);
    
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
                    $match = ["class" => $class, "method" => $meth, "args" => $this->cleanInputs($args) ];
                    Debug::debug("To: %s", $match);
                    
                    // Mathing all routes?
                    if ( ! $matchAll )
                    {
                        Debug::traceLeaveFunc( $match );
                        return $match;
                    }
                    // Accumlate matches
                    $retVal[] = $match;
                }
            }
        }
        // Here we return an array of matches (if we have one)
        Debug::traceLeaveFunc( $retVal );
        return $retVal;
    }

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