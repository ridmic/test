<?php namespace DryMile\Core\Utils;

require_once 'Secure.php';

class Cookie
{
    private $name       = false;
    private $value      = "";
    private $time       = 0;        // Dies at session close
    private $domain     = "";
    private $path       = "/";
    private $secure     = true;
    private $secret     = null;
    private $autoSign   = true;

    public function __construct( $name, $autoSign = true ) 
    {
        $this->setName( $name );
        $this->setEncryptKey( $name );
        $this->autoSign = $autoSign;
    }

    // Cookie management
    public function create() 
    {
        return setcookie( $this->name, $this->value, $this->time, $this->path, $this->domain, $this->secure,  true);
    }
    
    public function destroy()    { return $this->delete(); } // Synonym
    public function delete()
    {
        return setcookie( $this->name, '', 1, $this->getPath(), $this->getDomain(), $this->getSecure(),true);
    }
    
    // Cookie access    
    public function get()               { return $this->_get(); }
    public function set( $value )       { $this->value = $this->_set( $value ); }
    
    public function setDomain($domain)  { $this->domain = $domain; }
    public function getDomain()         { return $this->domain; }
    public function setName($id)        { $this->name = $id; }
    public function getName()           { return $this->name; }
    public function setPath($path)      { $this->path = $path; }
    public function getPath()           { return $this->path; }
    public function setSecure($secure)  { $this->secure = $secure; }
    public function getSecure()         { return $this->secure; }
    public function setEncryptKey($key) { $this->secret = Utils\Secure::hashToken($key); }
    public function getTime()           { return $this->time; }
    public function setTime($time)      
    {
        // Accept a time() value or a relative date string
        $this->time = $time;
        if ( is_string( $this->time ))
        {
            // Create a date
            $date = new \DateTime();
            // Modify it (+1hours; +1days; +20years; -2days etc)
            $date->modify($this->time);
            // Store the date in UNIX timestamp.
            $this->time = $date->getTimestamp();
        }
    }
    
    public function getEncrypted()
    {
        return Utils\Secure::decrypt( $this->get(), $this->secret);
    }
    
    public function setEncrypted($value)
    {
        $this->set( Utils\Secure::encrypt( $value, $this->secret ) );
    }
    
    protected function _set( $value )
    {
        if ( $this->autoSign )
        {
            $value = Utils\Secure::signValue( $value, $this->secret );
        }
        return $value;
    }
    
    protected function _get()
    {
        if ( isset($_COOKIE[$this->getName()]) )
        {
            $value = $_COOKIE[$this->getName()];
            if ( $this->autoSign )
            {
                $value = Utils\Secure::getSignedValue( $value, $this->secret );
            }
            return $value;
        }
        return null;
    }
}


