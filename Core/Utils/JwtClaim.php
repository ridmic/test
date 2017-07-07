<?php namespace Ridmic\Core\Utils;

require_once 'Secure.php';

/*
// Json Web token

iss: The issuer of the token
sub: The subject of the token
aud: The audience of the token
exp: This will probably be the registered claim most often used. This will define the expiration in NumericDate value. The expiration MUST be after the current date/time.
nbf: Defines the time before which the JWT MUST NOT be accepted for processing
iat: The time the JWT was issued. Can be used to determine the age of the JWT
jti: Unique identifier for the JWT. Can be used to prevent the JWT from being replayed. This is helpful for a one time use token.

*/

class Jwt
{
    public static function encode( $payload, $key, $algo = 'HS256' )
    {
        $header         = array('typ' => 'JWT', 'alg' => $algo);
        $segments       = array(Secure::urlsafeB64Encode(json_encode($header)),
                                Secure::urlsafeB64Encode(json_encode($payload)) );
        $signing_input  = implode('.', $segments);
        $signature      = self::sign($signing_input, $key, $algo);
        $segments[]     = Secure::urlsafeB64Encode($signature);
        return implode('.', $segments);
    }
    public static function encodeEncrypted( $payload, $key, $algo = 'HS256' )
    {
        $payload =  Secure::encrypt($payload, $key );
        if ( !is_null($payload) )
        {
            return self::encode( $payload, $key, $algo );   
        }
        return $payload;
    }
    
    public static function isValid( $jwt, $key, $algo = 'HS256' )
    {
        return is_null( self::decode($jwt, $key, $algo) ) ? false : true;
    }
    
    public static function decode( $jwt, $key, $algo = 'HS256' )
    {
        $retVal = null;
        $bits   = explode('.', $jwt);
        if (count($bits) == 3) 
        {
            list($headb64, $payloadb64, $cryptob64) = $bits;
            
            $header  = json_decode(Secure::urlsafeB64Decode($headb64));
            $payload = json_decode(Secure::urlsafeB64Decode($payloadb64), true);
            if ( !is_null($header) && !is_null($payload) )
            {
                $signature = Secure::urlsafeB64Decode($cryptob64);

                // We will only accept the algorithm we are expecting!
                if (is_string($header->alg) && strtoupper($header->alg) == strtoupper($algo) ) 
                {
                    if ( self::verifySignature($signature, "$headb64.$payloadb64", $key, $algo)) 
                    {
                       $retVal = $payload;
                    }
                }
            }
        }
        return $retVal;
    }
    public static function decodeEncrypted( $payload, $key, $algo = 'HS256' )
    {
        $payload = self::decode( $payload, $key, $algo );
        if ( !is_null($payload) )
        {
            return Secure::decrypt( $payload, $key );   
        }
        return $payload;
    }
    
    private static function verifySignature($signature, $input, $key, $algo)
    {
        switch ( strtoupper($algo) ) 
        {
            case 'HS256':
                return Secure::checkHash( self::sign($input, $key, strtoupper($algo)), $signature );
                break;
                
            case 'HS512':
                return Secure::checkHash( self::sign($input, $key, strtoupper($algo)), $signature );
                break;

            case 'NONE':
            default:
                return false;
        }
    }
    private static function sign($input, $key, $algo)
    {
        switch ( strtoupper($algo) ) 
        {
            case 'HS256':
                return Secure::hashToken($input, $key, true, 'sha256' );
                break;
                
            case 'HS512':
                return Secure::hashToken($input, $key, true, 'sha512' );
                break;

            case 'NONE':
            default:
                return false;
        }
    }
    
    public static function asArray( $jwt, $key, $algo = 'HS256' )
    {
        $retVal = '--invalid--';
        $bits   = explode('.', $jwt);
        if (count($bits) == 3) 
        {
            list($headb64, $payloadb64, $cryptob64) = $bits;
            
            $header  = json_decode(Secure::urlsafeB64Decode($headb64));
            $payload = json_decode(Secure::urlsafeB64Decode($payloadb64), true);
            if ( !is_null($header) && !is_null($payload) )
            {
                $signature = Secure::urlsafeB64Decode($cryptob64);

                // We will only accept the algorithm we are expecting!
                if (is_string($header->alg) && strtoupper($header->alg) == strtoupper($algo) ) 
                {
                    if ( self::verifySignature($signature, "$headb64.$payloadb64", $key, $algo)) 
                    {
                       $retVal = ['header' => $header, 'payload' => $payload ];
                    }
                }
            }
        }
        return $retVal;
    }
    
}

class JwtClaim
{
    protected $claims   = [];
    
    public function __construct()           { }
    
    public function getClaims()             { return $this->claims; }
    public function setClaims($claims)      { $this->claims = is_array($claims) ? $claims : []; }
    
    // iss: The issuer of the token
    public function setIssuer( $iss )       { $this->claims['iss'] = is_string($iss) ? $iss : '';  }
    public function getIssuer()             { return isset($this->claims['iss']) ? $this->claims['iss'] : null; }
    
    // sub: The subject of the token
    public function setSubject( $sub )      { $this->claims['sub'] = is_string($sub) ? $sub : '';  }
    public function getSubject()            { return isset($this->claims['sub']) ? $this->claims['sub'] : null; }
    
    // aud: The audience of the token
    public function setAudience( $aud )     { $this->claims['aud'] = is_string($aud) ? $aud : '';  }
    public function getAudience()           { return isset($this->claims['aud']) ? $this->claims['aud'] : null; }
    
    // exp: This will probably be the registered claim most often used. 
    // This will define the expiration in NumericDate value. The expiration MUST be after the current date/time.
    public function setExpiration( $exp )   { $this->claims['exp'] = is_integer($exp) ? $exp : time();  }
    public function getExpiration()         { return isset($this->claims['exp']) ? $this->claims['exp'] : null; }
    public function setExpirationTo($time)      
    {
        // Accept a time() value or a relative date string
        if ( is_string( $time ))
        {
            // Create a date
            $date = new \DateTime();
            // Modify it (+1hours; +1days; +20years; -2days etc)
            $date->modify($time);
            // Store the date in UNIX timestamp.
            $time = $date->getTimestamp();
        }
        $this->setExpiration($time);
    }
    
    // nbf: Defines the time before which the JWT MUST NOT be accepted for processing
    public function setNotBefore( $nbf )    { $this->claims['nbf'] = is_integer($nbf) ? $nbf : time();  }
    public function getNotBefore()          { return isset($this->claims['nbf']) ? $this->claims['nbf'] : null; }
    
    // iat: The time the JWT was issued. Can be used to determine the age of the JWT
    public function setIssued( $iat )       { $this->claims['iat'] = is_integer($iat) ? $iat : time();  }
    public function getIssued()             { return isset($this->claims['iat']) ? $this->claims['iat'] : null; }
    
    // jti: Unique identifier for the JWT. Can be used to prevent the JWT from being replayed. 
    // This is helpful for a one time use token.
    public function setIdentifier( $jti )   { $this->claims['jti'] = is_string($jti) ? $jti : '';  }
    public function getIdentifier()         { return isset($this->claims['jti']) ? $this->claims['jti'] : null; }
    
    // Although we can add custom claims anywhere, 'Context' is where we add our custom claims to keep them consistent
    public function setContextClaim( $claims = [] )     { if ( is_array($claims) ) $this->claims['Context'] = $claims; }
    public function addContextClaim( $name, $value )    { if ( !is_null($name) && !is_null($value) ) $this->claims['Context'][$name] = $value; }
    public function getContextClaim($name)              { return isset($this->claims['Context'][$name]) ? $this->claims['Context'][$name] : null; }
    public function contextClaims()                     { return isset($this->claims['Context']) ? $this->claims['Context'] : []; }
    
    // Some wrappers to build the Jwt
    public function encode( $key, $encrypted = false )
    {
        if ( $encrypted )
        {
            return Jwt::encodeEncrypted( $this->getClaims(), $key );
        }
        return Jwt::encode( $this->getClaims(), $key );
    }
    
    public function decode( $token, $key, $encrypted = false )
    {
        $decoded = null;
        if ( $encrypted )
        {
            $decoded = Jwt::decodeEncrypted( $token, $key );
        }
        else
        {
            $decoded = Jwt::decode( $token, $key );
        }
        $this->setClaims( $decoded );
        return is_null( $decoded ) ? false : true;
    }  

    public function hasExpired()
    {   
        $expires = $this->getExpiration();
        if ( !is_null($expires) && $expires > 0 )
            return  $expires < time() ? true : false;  
        return false;   // No expiry or 0
    }
    
    public function hasStarted()
    {   
        $notBefore = $this->getNotBefore();
        if ( !is_null($notBefore) )
            return  $notBefore < time() ? true : false;  
        return true;   // No limit
    }
    
    public function isActive()
    {
        return $this->hasStarted() && !$this->hasExpired();
    }

}

