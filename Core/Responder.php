<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";
include_once "ResponseCode.php";

class Responder
{
    protected $headers      = [];
    protected $showResponse = true;

    public function __construct( $showResponse = true )
    {
        $this->showResponse = $showResponse;    
    }
    public function showResponse( $bool )  { $this->showResponse = is_bool($bool) ? $bool : false; }
    
    public function header( $header )  { $this->header[] = $header; }
    public function getHeaders()       { return $this->headers; }
    public function sendHeaders()
    {
        if  (!headers_sent() )
        {
            foreach ( $this->header as $header )
                header($header);
        }
    }

    public function respond( ResponseCode $response )
    {
        if ( !$response->isOK() || $this->showResponse )
        {
            echo $this->response( $response );
        }
    }
    
    protected function response( ResponseCode $response )
    {
        return (string)"RESPONSECODE [".$response->code()."] = ".$response->status()." {".implode('|',$response->response())."}<br>\n";
    }
}

class ResponderJson extends Responder
{
    protected function response( ResponseCode $response )
    {
        $this->header("HTTP/1.1 " . $response->code() . " " . $response->status());
        $this->header('Content-Type: application/json; charset=utf8');
        $this->header('X-Content-Type-Options: nosniff');
        $this->sendHeaders();

         return json_encode( $response->response());
    }
}

class ResponderHtml extends Responder
{
    protected function response( ResponseCode $response )
    {
        $this->header("HTTP/1.1 " . $response->code() . " " . $response->status());
        $this->header('Content-Type: text/html; charset=utf8');
        $this->header('X-Content-Type-Options: nosniff');
        $this->sendHeaders();
        
        return $this->_encode($response->response());
    }

    protected function _encode( $data )
    {
        if ( is_array($data) )
        {
            foreach ($data as $name => $value ) 
            {
                $output .= "{";
                if ( is_array($value) )
                    $output .= $this->_encode( $value );
                else
                    $output .= htmlentities($name) . " => " . htmlentities($value);
                $output .= "}";
            }    
        }
        else 
            $output = htmlentities($data);
            
        return $output;
    }
}
