<?php namespace DryMile\Core\Utils;

// Static classs to be used by anything that requires encryption, hashing or secure tokens
class Secure
{
    const DEFAULT_ALGORITHM  = 'sha512';
    
    protected function __construct() { } 
    
    // Generate a random token : can be used for salt in encryption or as a 'secret' key
    public static function generateToken( $length = 64, $mode='bin2hex', $strong = true )
    {
        $token = openssl_random_pseudo_bytes($length, $strong);
        switch ( strtolower($mode) )
        {
            case 'base64':
                $token = self::urlSafeB64Encode($token);
                break;
            default:
            case 'bin2hex':
                $token = bin2hex($token);
                break;
        }
        return substr( $token, 0, $length );
    }
    
    // Hash functions (returns a string that is 128 bytes long)
    public static function hashToken( $token, $secret=null, $raw = false, $algo=self::DEFAULT_ALGORITHM )        
    { 
        if ( !is_null($secret) )
            return hash_hmac($algo, $token, $secret, $raw );
         return hash($algo, $token ); 
    }
    public static function hashInsecureToken( $token )        
    { 
        return md5( $token ); 
    }
    public static function checkHash( $hash1, $hash2 )
    {
        if ( is_string($hash1) && is_string($hash2) )
            return (md5($hash1) === md5($hash2));
        return false;
    }

    // Simple encryption
    // To sign your encryption just use $enc = CUtils\Secure::signValue( CUtils\Secure::encrypt( $payload, $secret ), $secret );
    public static function encrypt( $data, $secret, $algo=self::DEFAULT_ALGORITHM, $sep = ':' )
    {
        // Max secret length = 32 for this algorithm
        $secret     = substr( self::hashToken($secret), 0, 32 );
        $data       = serialize($data);
        $iv         = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $encrypted  = openssl_encrypt( $data, 'AES-256-CBC', $secret, 0, $iv);        
        $encoded    = self::urlSafeB64Encode($encrypted) . $sep . self::urlSafeB64Encode($iv);
        
        return $encoded;
    }
    // simple decryption
    // To un-sign your encryption just use $dec = CUtils\Secure::decrypt( CUtils\Secure::getSignedValue($enc, $secret ), $secret );
    public static function decrypt( $data, $secret, $sep = ':' )
    {
        // Max secret length = 32 for this algorithm
        $secret    = substr( self::hashToken($secret), 0, 32 );
        $decrypted = false;
        $data      = explode($sep, $data );
        if ( count($data) == 2 )
        {
            $decoded = self::urlSafeB64Decode($data[0]);
            $iv      = self::urlSafeB64Decode($data[1]);
            if ( strlen($iv) == openssl_cipher_iv_length('AES-256-CBC') )
            { 
                $decrypted = openssl_decrypt($decoded, 'AES-256-CBC', $secret, 0, $iv );
                $decrypted = unserialize($decrypted);
            }
        }
        return $decrypted;
    }
    
    // generic signing a value - to prevent modification of a value (see also JWT for signed web tokens)
    public static function signValue( $value, $secret, $sep = '|' )
    {
        $hash       = self::hashToken( $secret );
        $signature  = self::hashToken( $hash . $value );            
        return $signature . $sep . $value ;
    }
    public static function getSignedValue( $value, $secret, $sep = '|' )
    {
        $bits       = explode($sep, $value );
        $hash       = self::hashToken( $secret );
        $signature  = self::hashToken( $hash . $bits[1] );  
        if ( self::checkHash( $bits[0], $signature ) ) 
            return $bits[1];
        return null;        
    }
    
    // Password functions (for securing plain text 'raw' passwords or pre-hashed passwords)
    public static function passwordHash( $pw, $algo=self::DEFAULT_ALGORITHM )
    {
        return password_hash( self::urlSafeB64Encode( self::hashToken($pw, null, true) ), PASSWORD_DEFAULT );
    }
    
    public static function passwordVerify( $pw, $pwHash, $algo=self::DEFAULT_ALGORITHM )
    {
        return password_verify( self::urlSafeB64Encode( self::hashToken($pw, null, true) ), $pwHash );
    } 
    
    public static function urlSafeB64Encode($data)
    {
        $b64 = base64_encode($data);
        $b64 = str_replace(array('+', '/', '\r', '\n', '='), array('-', '_'), $b64);
        return $b64;
    }
    public static function urlSafeB64Decode($b64)
    {
        $b64 = str_replace(array('-', '_'), array('+', '/'), $b64);
        return base64_decode($b64);
    }    
}