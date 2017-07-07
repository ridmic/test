<?php namespace Ridmic\Core\Utils;

class Session 
{
    private $encKey, $name, $cookie, $fingerPrint;

    public function __construct( $name = 'MY_SESSION', $cookie = [] )
    {
        $this->name         = $name;
        $this->fingerPrint  = md5( "FINGER".Input::server('HTTP_USER_AGENT')."PRINT" );
        $this->cookie       = [ 'lifetime' => 0,
                                'path'     => ini_get('session.cookie_path'),
                                'domain'   => ini_get('session.cookie_domain'),
                                'secure'   => Input::hasServer('HTTPS'),
                                'httponly' => true ];
        $this->setup($cookie);
    }

    protected function setup( $cookie = [] )
    {
        $this->encKey = Secure::hashToken($this->name);
        $this->cookie = array_merge( $this->cookie, $cookie );

        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);

        session_name($this->name);
        session_set_cookie_params( $this->cookie['lifetime'], $this->cookie['path'],
                                   $this->cookie['domain'], $this->cookie['secure'],
                                   $this->cookie['httponly'] );
    }
    public function setFingerprint( $fp = null )
    {
        $this->fingerPrint = null;  // Clear it
        if ( is_string($fp) && $fp != '' )
            $this->fingerPrint = $fp;
    }
    
    public function start()
    {
        session_start();
        return (mt_rand(0, 4) === 0) ? $this->refresh() : true; // 1/5 refresh
    }
    
    public function destroy()
    {
        $_SESSION = [];
        setcookie( $this->name, '', 1, $this->cookie['path'], $this->cookie['domain'],
                   $this->cookie['secure'], $this->cookie['httponly'] );
        return session_destroy();
    }
    
    public function refresh()
    {
        return session_regenerate_id(true);
    }
    
    public function isExpired($ttl = 30 )
    {
        $activity = isset($_SESSION['_last_activity']) ? $_SESSION['_last_activity'] : false;
        if ($activity !== false && time() - $activity > $ttl * 60 )
            return true;

        $_SESSION['_last_activity'] = time();
        return false;
    }
    
    public function isFingerprint()
    {
        // Allow this to be disabled!
        if ( isset( $this->fingerPrint ) && is_string( $this->fingerPrint ) )
        {
            if ( isset($_SESSION['_fingerprint']) )
                return $_SESSION['_fingerprint'] ===  $this->fingerPrint;
    
            $_SESSION['_fingerprint'] = $this->fingerPrint;
        }
        return true;
    }
    
    public function isValid($ttl = 30)
    {
        return !$this->isExpired($ttl) && $this->isFingerprint();
    }

    public function get( $key, $default = null )
    {
        if ( isset($_SESSION[$key]) )
            return $_SESSION[$key];
        return $default;
    }
    
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }    

    public function exists( $keys )
    {
        $keys = is_array($keys) ? $keys : array( $keys );
        foreach ( $keys as $key )
        {
            if ( !isset($_SESSION[$key]) )
            {
                return false;
            }
        }
        return true;
    }
    
    public function clear($key=null)
    {
        if ( is_null( $key ) )
        {
            $_SESSION = [];
        }
        else if ( isset($_SESSION[$key]) )
        {
            unset($_SESSION[$key]);
        }
    }    

    public function getEncrypted( $key, $default = null )
    {
        if ( isset($_SESSION[$key]) )
            return Secure::decrypt( $_SESSION[$key], $this->encKey);
        return $default;
    }
    
    public function setEncrypted($key, $value)
    {
        $_SESSION[$key] = Secure::encrypt( $value, $this->encKey );
    }
    
    public function getSerialized( $key, $default = null )
    {
        if ( isset($_SESSION[$key]) )
            return unserialize( base64_decode($_SESSION[$key]) );
        return $default;
    }
    
    public function setSerialized($key, $value)
    {
        $_SESSION[$key] = base64_encode( serialize($value) );
    }
    
}

