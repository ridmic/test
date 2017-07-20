<?php namespace DryMile\Core\Utils;

require_once "Input.php";

class Header
{
    public function getBestSupportedMimeType( $mimeTypes = null, $default = null )
    {
        return $this->getBestSupportedAccept($mimeTypes, $default);    
    }
    
    protected function getBestSupportedAccept( $mimeTypes = null, $default = null) 
    {
        // Values will be stored in this array
        $AcceptTypes = Array ();
    
        // Accept header is case insensitive, and whitespace isn’t important
        $accept = strtolower(str_replace(' ', '', Input::server('HTTP_ACCEPT')));
        
        // divide it into parts in the place of a ","
        $accept = explode(',', $accept);
        foreach ($accept as $a) 
        {
            // the default quality is 1.
            $q = 1;
            // check if there is a different quality
            if (strpos($a, ';q=')) 
            {
                // divide "mime/type;q=X" into two parts: "mime/type" i "X"
                list($a, $q) = explode(';q=', $a);
            }
            // mime-type $a is accepted with the quality $q
            // WARNING: $q == 0 means, that mime-type isn’t supported!
            $AcceptTypes[$a] = $q;
        }
        arsort($AcceptTypes);
    
        // if no parameter was passed, just return parsed data
        if (!$mimeTypes) return $AcceptTypes;
    
        $mimeTypes = array_map('strtolower', (array)$mimeTypes);
    
        // let’s check our supported types:
        foreach ($AcceptTypes as $mime => $q) 
        {
           if ($q && in_array($mime, $mimeTypes)) return $mime;
        }
        // no mime-type found
        return $default;
    }    
}