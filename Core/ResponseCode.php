<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";

class ResponseCode extends Object
{
    const       CODE_OK                     = 200;
    const       CODE_CREATED                = 201;
    const       CODE_NOTMODIFIED            = 304;
    const       CODE_BADREQUEST             = 400;
    const       CODE_UNAUTHORIZED           = 401;
    const       CODE_FORBIDDEN              = 403;
    const       CODE_NOTFOUND               = 404;
    const       CODE_NOTALLOWED             = 405;
    const       CODE_NOTACCEPTABLE          = 406;
    const       CODE_INTERNALERROR          = 500;
    const       CODE_UNDEFINEDERROR         = 501;

    protected $statusHTML = array(    
                            100 => 'Continue',
                		    101 => 'Switching Protocols',
                		    200 => 'OK',
                		    201 => 'Created',
                		    202 => 'Accepted',
                		    203 => 'Non-Authoritative Information',
                		    204 => 'No Content',
                		    205 => 'Reset Content',
                		    206 => 'Partial Content',
                		    300 => 'Multiple Choices',
                		    301 => 'Moved Permanently',
                		    302 => 'Found',
                		    303 => 'See Other',
                		    304 => 'Not Modified',
                		    305 => 'Use Proxy',
                		    306 => '(Unused)',
                		    307 => 'Temporary Redirect',
                		    400 => 'Bad Request',
                		    401 => 'Unauthorized',
                		    402 => 'Payment Required',
                		    403 => 'Forbidden',
                		    404 => 'Not Found',
                		    405 => 'Method Not Allowed',
                		    406 => 'Not Acceptable',
                		    407 => 'Proxy Authentication Required',
                		    408 => 'Request Timeout',
                		    409 => 'Conflict',
                		    410 => 'Gone',
                		    411 => 'Length Required',
                		    412 => 'Precondition Failed',
                		    413 => 'Request Entity Too Large',
                		    414 => 'Request-URI Too Long',
                		    415 => 'Unsupported Media Type',
                		    416 => 'Requested Range Not Satisfiable',
                		    417 => 'Expectation Failed',
                		    500 => 'Internal Server Error',
                		    501 => 'Not Implemented',
                		    502 => 'Bad Gateway',
                		    503 => 'Service Unavailable',
                		    504 => 'Gateway Timeout',
                		    505 => 'HTTP Version Not Supported'
                		    );
                		    
    protected $response     = array();
    protected $code         = 0;
    protected $codeOK       = self::CODE_OK;
    protected $codeDefault  = self::CODE_UNDEFINEDERROR;
    protected $type         = 'html';

    function __construct( $code, $response = null )
    {
        $this->setCode( $code );
        $this->setResponse( 'code', $this->code );
        $this->addResponse( 'status', $this->status($this->code));
        if ( !is_null($response) )
            $this->addResponse( 'response', $response );
    }

    function setCode($code)                 { $this->code = $this->validCode( $code ); }
    function setResponse( $name, $value )   { $this->response = []; $this->response[$name] = $value;  }  
    function addResponse( $name, $value )   { $this->response[$name] = $value;  }  
    function loadResponse( $response )      { $this->response = $response;  }  
    
    function response()                     { return $this->response; }
    function code()                         { return $this->code; }
    function isOK()                         { return $this->code == $this->codeOK; }
    function status( $code=null ) 
    {
        $code = is_null($code) ? $this->code : $code;
        return ( array_key_exists( $code, $this->statusHTML ) ? $this->statusHTML[$code] : $this->statusHTML[$this->codeDefault] ); 
    }

    function validCode( $code ) 
    {
        $code = is_null($code) ? $this->code : $code;
        return ( array_key_exists( $code, $this->statusHTML ) ? $code : $this->codeDefault ); 
    }

    // Magic Functions
    public function __toString()
    {
        return (string)"RESPONSECODE [".$this->code()."] = ".$this->status()." {".implode('|',$this->response())."}<br>\n";
    }      
}
