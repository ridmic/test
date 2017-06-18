<?php
namespace Ridmic\Core\Utils;

class Input
{
    // GET
    public static function get( $name )             { return filter_input( INPUT_GET, $name, FILTER_SANITIZE_STRING ); }
    public static function getEmail( $name )        { return filter_input( INPUT_GET, $name, FILTER_SANITIZE_EMAIL );  }
    public static function getInt( $name )          { return filter_input( INPUT_GET, $name, FILTER_SANITIZE_NUMBER_INT );  }
    public static function getEncoded( $name )      { return filter_input( INPUT_GET, $name, FILTER_SANITIZE_ENCODED );  }

    // POST
    public static function post( $name )            { return filter_input( INPUT_POST, $name, FILTER_SANITIZE_STRING ); }
    public static function postEmail( $name )       { return filter_input( INPUT_POST, $name, FILTER_SANITIZE_EMAIL );  }
    public static function postInt( $name )         { return filter_input( INPUT_POST, $name, FILTER_SANITIZE_NUMBER_INT );  }
    public static function postEncoded( $name )     { return filter_input( INPUT_POST, $name, FILTER_SANITIZE_ENCODED );  }

    // REQUEST
    public static function request($name)       
    {
        if ( self::hasGet($name) )      { return self::get($name);  }
        if ( self::hasPost($name) )     { return self::post($name); }
        return null;
    }

    // SERVER
    public static function server( $name )          { return filter_var( $_SERVER[$name], FILTER_SANITIZE_STRING ); }
    public static function serverRaw( $name )       { return $_SERVER[$name]; }
    public static function serverEncoded( $name )   { return filter_input( INPUT_SERVER, $name, FILTER_SANITIZE_ENCODED );  }
    public static function serverGetHeaders()
    {
        // getallheaders available, use that
        if (function_exists('getallheaders')) 
        {
            return getallheaders();
        }
        // getallheaders not available: manually extract 'm
        $headers = array();
        foreach ($_SERVER as $name => $value) 
        {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) 
            {
                $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = Input::sanitize($value);
            }
        }
        return $headers;
    }

    // Generic
    public static function sanitize( $value )       { return filter_var( $value, FILTER_SANITIZE_STRING ); }
    public static function validEmail( $value )     { return filter_var( $value, FILTER_VALIDATE_EMAIL ); }
    public static function validInt( $value )       { return filter_var( $value, FILTER_VALIDATE_INT ); }
    public static function validFloat( $value )     { return filter_var( $value, FILTER_VALIDATE_FLOAT ); }
    public static function validIP( $value )        { return filter_var( $value, FILTER_VALIDATE_IP ); }
    public static function validURL( $value )       { return filter_var( $value, FILTER_VALIDATE_IP ); }


    // Helpers
    public static function hasGet( $vars )          { return self::hasVars($_GET, $vars ); }
    public static function hasPost( $vars )         { return self::hasVars($_POST, $vars ); }
    public static function hasServer( $vars )       { return self::hasVars($_SERVER, $vars ); }
    public static function hasVars( $array, $vars )   
    {
        $vars = is_array($vars) ? $vars : array( $vars );
        foreach ( $vars as $var )
        {
            if ( !isset($array[$var]) )
            {
                return false;
            }
        }
        return true;
    }
    
}

?>