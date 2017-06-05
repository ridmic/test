<?php
namespace Ridmic\Core;

include_once "core/object.php";

class Router extends Object {

    const REGVAL = '#({:.+?})#';    
    
    protected $routes = [
        'get'       => [],
        'post'      => [],
        'put'       => [],
        'delete'    => [],
        'options'   => [],
        'patch'     => [],
        //'head'       => [],
        'any'       => []
    ];

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

    public function any($pattern, $handler, $name = '' )
    {
        $this->addRoute('any', $pattern, $handler, $name );
        return $this;
    }
    
    public function get($pattern, $handler, $name = '' )
    {
        $this->addRoute('get', $pattern, $handler, $name );
        return $this;
    }
    
    public function post($pattern, $handler, $name = '' )
    {
        $this->addRoute('post', $pattern, $handler, $name);
        return $this;
    }
    
    public function put($pattern, $handler, $name = '')
    {
        $this->addRoute('put', $pattern, $handler, $name );
        return $this;
    }
    
    public function delete($pattern, $handler, $name = '' )
    {
        $this->addRoute('delete', $pattern, $handler, $name);
        return $this;
    }
    
    public function patch($pattern, $handler, $name = '')
    {
        $this->addRoute('patch', $pattern, $handler, $name );
        return $this;
    }

    public function options($pattern, $handler, $name = '' )
    {
        $this->addRoute('options', $pattern, $handler, $name);
        return $this;
    }

    public function addRoute($method, $pattern, $handler , $name = '')
    {
        $this->traceEnterFunc();

        $this->routes[$method][$pattern] = [$name => $handler];
        if ( is_array($handler) )
          $this->debug( "Routing: %s:%s to '%s' => '%s@%s'", $method, $pattern, $name, "".$handler[0], "".$handler[1] );
        else
          $this->debug( "Routing: %s:%s to '%s' => '%s'", $method, $pattern, $name, $handler );
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
        $this->traceLeaveFunc();
    }

    public function match( $m, $request )
    {
        $this->traceEnterFunc();
        
        $retVal  = [];
        $methods = array( $m, 'any' );
        $this->debug("Matching: %s:%s", $method, $request);
        foreach ( $methods as $method )
        {
            foreach ($this->routes[$method] as $pattern => $handler) 
            {
                $this->debug("Against: %s:%s", $method, $pattern );
                $args   = []; 
                $class  = null;
                list($name, $meth) = each($handler); 

                // Check for a regex type path
                if ( preg_match(self::REGVAL, $pattern) )
                {
                    list($args, $uri, $pattern) = $this->parseRegexRoute($request, $pattern); 
                    $this->debug("Expanding to: %s:%s", $method, $pattern );
                }
    
                // Do we have a match?
                if ( !preg_match(($this->exactMatch ? "#^$pattern$#" : "#^$pattern#"), $request) )
                    continue ;

                $this->debug("Matched: $request");

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
                $this->traceLeaveFunc( $retVal );
                return $retVal;
            }
        }
        $this->traceLeaveFunc( $retVal );
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