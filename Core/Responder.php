<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";
include_once "ResponseCode.php";

class Responder
{
    const       TYPE_TEXT   = 0;
    const       TYPE_JSON   = 1;
    const       TYPE_HTML   = 2;
    const       TYPE_XML    = 3;

    protected $headers      = [];
    protected $showResponse = true;
    protected $responseType = Responder::TYPE_TEXT;

    public function __construct( $type = Responder::TYPE_TEXT, $showResponse = true )
    {
        $this->responseType = $type;    
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
            switch ( $this->responseType )
            {
                case Responder::TYPE_HTML:
                    $this->header("HTTP/1.1 " . $response->code() . " " . $response->status());
                    $this->header('Content-Type: text/html; charset=utf8');
                    $this->header('X-Content-Type-Options: nosniff');
                    $this->sendHeaders();
                    
                    echo $this->_encodeHtml($response->response());
                    break;
                    
                case Responder::TYPE_JSON:
                    $this->header("HTTP/1.1 " . $response->code() . " " . $response->status());
                    $this->header('Content-Type: application/json; charset=utf8');
                    $this->header('X-Content-Type-Options: nosniff');
                    $this->sendHeaders();
            
                    echo json_encode( $response->response() );
                    break;
                    
                case Responder::TYPE_XML:
                    $this->header("HTTP/1.1 " . $response->code() . " " . $response->status());
                    $this->header('Content-Type: application/xml; charset=utf8');
                    $this->header('X-Content-Type-Options: nosniff');
                    $this->sendHeaders();
            
                    // creating object of SimpleXMLElement
                    $xml = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
                    $this->_encodeXml($response->response(), $xml);
                    echo $xml->asXML();
                    break;
                    
                case Responder::TYPE_TEXT:
                default:
                    echo "RESPONSECODE [".$response->code()."] = ".$response->status()." {".implode('|',$response->response())."}<br>\n";
                    break;
            }
        }
    }
    
    protected function _encodeHtml( $data )
    {
        if ( is_array($data) )
        {
            foreach ($data as $name => $value ) 
            {
                $output .= " {";
                if ( is_array($value) )
                    $output .= htmlentities($name) . " => [". $this->_encodeHtml( $value ) ."]";
                else
                    $output .= htmlentities($name) . " => " . htmlentities($value);
                $output .= "} ";
            }    
        }
        else 
            $output = htmlentities($data);
            
        return $output;
    }
    
    protected function _encodeXML( $data, \SimpleXMLElement $xml )
    {
        foreach ($data as $k => $v)
        {
            is_array($v) ? $this->_encodeXML($v, $xml->addChild($k)) : $xml->addChild($k, $v);
        }
        return $xml;
    }
}
