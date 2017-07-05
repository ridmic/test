<?php
namespace Ridmic\Core;

include_once "Utils/Input.php";
include_once "Debug.php";
include_once "Object.php";

use \Ridmic\Core\Utils\Input    as Input;

class RouteList extends Object
{
    const       REGVAL          = '/({:.+?})/';    
    protected   $patterns       = [ ':any'      => '(.*)',
                                    ':id'       => '([0-9]+)',
                                    ':slug'     => '([a-z\-]+)',
                                    ':name'     => '([a-zA-Z]+)',
                                    ':hex'      => '([0-9a-fA-F]+)',
                                    ':alphanum' => '([0-9a-zA-Z]+)'
                                  ];

    protected   $allowedMethods = 'GET|POST|PUT|DELETE|PATCH|HEAD';
    protected   $routes         = [];
    protected   $baseRoute      = '';
    protected   $exactMatch     = true;
    protected   $caseMatch      = false;
    
    public function setBaseRoute( $br )     { $this->baseRoute =  '/' . trim($br, '/'); }
    public function baseRoute( $br )        { return $this->baseRoute;  }
    
    public function allowedMethod( $method ) 
    {
        return in_array($method, explode('|', $this->allowedMethods));
    }

    public function add( $methods, $pattern, $fn )
    {
        Debug::traceEnterFunc();

        // build our route
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        
        // Add the routes
        $methods = strtoupper($methods);
        $methods = $methods == 'ALL' ? $this->allowedMethods : $methods;
        $methods = explode('|', $methods);
        foreach ( $methods as $method ) 
        {
            if ( $this->allowedMethod($method) )
            {
                $this->routes[$method][] = array( 'pattern' => $pattern, 'fn' => $fn );
                
                if ( is_array($fn) )
                  Debug::debug( "Routing: %s:%s to %s@%s", $method, $pattern, "".$fn[0], "".$fn[1] );
                else
                  Debug::debug( "Routing: %s:%s to %s'", $method, $pattern, $fn );
           }
        }
        Debug::traceLeaveFunc();
    }

    public function match( $method, $uri, $matchAll = true )
    {
        Debug::traceEnterFunc();

        // Loop all routes
        $matchedRoutes   = [];
        $matchePatterns  = [];
        $method  = strtoupper($method);
        if ( isset($this->routes[$method]) )
        {
            Debug::debug("Matching: %s:%s", $method, $uri );
            
            foreach ( $this->routes[$method] as $route ) 
            {
                // Replace our know parameter regex's
                $pattern = $route['pattern'];
                
                // Allow route specific override of exact matching
                $exactMatch = substr($pattern, -1) == '#' ? false : $this->exactMatch;
                $pattern    = substr($pattern, -1) == '#' ? substr($pattern, 0, -1) : $pattern;


                Debug::debug("Against: %s:%s", $method, $pattern );
                
                $pattern = preg_replace_callback(self::REGVAL, function($matches) 
                                                             {
                                                                $patterns   = $this->patterns; 
                                                                $matches[0] = str_replace(['{', '}'], '', $matches[0]);
                                                                if ( in_array($matches[0], array_keys($patterns)) )
                                                                {                       
                                                                    return  $patterns[$matches[0]];
                                                                }
                                                                return ltrim($matches[0], ':');
                                                             }, $pattern );
                // we have a match!
                $match = $exactMatch ? "#^{$pattern}$#" : "#^{$pattern}#";
                $match = $this->caseMatch  ? $match : $match.'i';
                if (preg_match_all($match, $uri, $matches, PREG_OFFSET_CAPTURE)) 
                {
                    // Rework matches to only contain the matches, not the orig string
                    $matches = array_slice($matches, 1);
                    // Extract the matched URL parameters (and only the parameters)
                    $params = array_map(function ($match, $index) use ($matches) 
                                        {
                                            // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                                            if (isset($matches[$index+1]) && isset($matches[$index+1][0]) && is_array($matches[$index+1][0])) 
                                            {
                                                return trim(substr($match[0][0], 0, $matches[$index+1][0][1] - $match[0][1]), '/');
                                            } 
                                            else // We have no following parameters: return the whole lot
                                            {
                                                return (isset($match[0][0]) ? trim($match[0][0], '/') : null);
                                            }
                                        }, $matches, array_keys($matches));
                                        
                   
                    
                    // call the handling function with the URL parameters
                    $matched            = [ 'fn' => $route['fn'], 'params' => $this->cleanInputs($params)];
                    $matchePatterns[]   = $pattern;
                    $matchedRoutes[]    = $matched;

                    // If we need to quit, then quit
                    if (!$matchAll) 
                    {
                        break;
                    }
                }
            }
        }
        if ( empty($matchedRoutes) )
            Debug::debug("No Matches" );
        else
            Debug::debug("Matched %s", $matchePatterns );
        
        // Return the number of routes handled
        Debug::traceLeaveFunc( $matchedRoutes );
        return $matchedRoutes;
    }
    
    public function routes()        { return $this->routes; }
    public function hasRoutes()     { return ! empty($this->routes); }
    
    
    // Clean up any input parameters
    public function cleanInputs( $data ) 
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

class Router
{
    protected   $tokenAuth  = 'x-api-key';
    protected   $routes     = null;
    protected   $before     = null;
    protected   $after      = null;
    protected   $args       = [];
    protected   $argsUri    = [];

    public function __construct( $versioned = false )
    {
        $this->before   = new RouteList;      // #1 : All of the matches here get called
        $this->routes   = new RouteList;      // #2 : Only the first match of this gets called
        $this->after    = new RouteList;      // #3 : All of the matches here get called
        
        // Pull in the parameters
        $method         = $this->getRequestMethod();
        $this->argsUri  = $this->routes->cleanInputs($_GET);
        $this->args     = [];
        switch( $method )
        {
            // GET should be read only!
            case 'GET':
                break;
            // POST ususally would map to a 'create' operation
            case 'POST':
                $this->args = $this->routes->cleanInputs($_POST);
                break;
            // DELETE ususally would map to a 'delete' operation
            case 'DELETE':
            // PUT ususally would map to an 'update/set' operation
            case 'PUT':
                // basically, we read a string from PHP's special input location,  
                // and then parse it out into an array via parse_str... per the PHP docs:  
                // Parses str as if it were the query string passed via a URL and sets  
                // variables in the current scope.  
                $body           = file_get_contents('php://input');  
                $parameters     = [];
                $content_type   = Input::hasServer('CONTENT_TYPE') ? Input::server('CONTENT_TYPE') : false;
                switch($content_type) 
                {
                    case "application/json":
                        $body_params = json_decode($body);
                        if($body_params) 
                        {
                            foreach($body_params as $param_name => $param_value) 
                            {
                                $parameters[$param_name] = $param_value;
                            }
                        }
                        $this->format = "json";
                        break;
                    case "application/x-www-form-urlencoded":
                        parse_str($body, $postvars);
                        foreach($postvars as $field => $value) 
                        {
                            $parameters[$field] = $value;
                        }
                        $this->format = "html";
                        break;

                    default:
                        break;
                }
                $this->args = $this->routes->cleanInputs($parameters);
                break;
                
            default:
                break;
        }
    }
    
    public function allowedMethod( $method ) 
    {
        return $this->routes->allowedMethod( $method );
    }
    
    public function setBaseRoute( $br )
    {
        $this->before->setBaseRoute( $br );
        $this->routes->setBaseRoute( $br );
        $this->after->setBaseRoute( $br );
    }

    public function before()    {  return $this->before; }
    public function route()     {  return $this->routes; }
    public function after()     {  return $this->after; }

    public function getCurrentUri()
    {
        // Get the current Request URI and remove rewrite basepath from it (= allows one to run the router in a subfolder)
        $basepath = implode('/', array_slice(explode('/', Input::server('SCRIPT_NAME')), 0, -1)) . '/';
        $uri      = substr(Input::server('REQUEST_URI'), strlen($basepath));
        // Don't take query params into account on the URL
        if (strstr($uri, '?')) 
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        // Remove trailing slash + enforce a slash at the start 
        $uri = '/' . ltrim($uri, '/');
        return $uri;
    }

    public function getRequestMethod()
    {
        // Take the method as found in $_SERVER
        $method = Input::server('REQUEST_METHOD');
        // If it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if (Input::server('REQUEST_METHOD') == 'HEAD') 
        {
            ob_start();
            $method = 'GET';
        } // If it's a POST request, check for a method override header
        elseif ( Input::server('REQUEST_METHOD') == 'POST') 
        {
            $headers = Input::serverGetHeaders();
            if (isset($headers['x-http-method-override']) && in_array($headers['x-http-method-override'], array('PUT', 'DELETE', 'PATCH'))) 
            {
                $method = $headers['x-http-method-override'];
            }
            elseif (isset($headers['x-http-method']) && in_array($headers['x-http-method'], array('PUT', 'DELETE', 'PATCH'))) 
            {
                $method = $headers['x-http-method'];
            }
        }
        return strtoupper($method);
    }
    
    public function closeRequestMethod()
    {
        // If it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if (Input::server('REQUEST_METHOD') == 'HEAD') 
        {
            ob_end_clean();
        }
    }
    
    public function getAuthenticationToken()
    {
        $token   = '';
        $headers = Input::serverGetHeaders();
        $token   = isset($headers[$this->tokenAuth]) ?  $headers[$this->tokenAuth] : '';
        return $token;
    }
    
}

