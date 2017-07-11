<?php namespace DryMile\Core\Utils;

class FileCache 
{
    protected   $pathToCache = '';
   
    public function __construct()
    {
    }

    public function setCachePath( $path )
    {
        $this->pathToCache = $path;    
    }
    
    // ------------------------------------------------------------
    
    // This is the function you store information with
    function store( $key, $data, $ttl ) 
    {
        $retVal = false;
        
        // Opening the file in read/write mode
        $h = fopen( $this->getFileName($key),'a+');
        if ($h !== false )
        {
            // exclusive lock, will get released when the file is closed
            flock($h,LOCK_EX); 
            // go to the start of the file
            fseek($h,0); 
            // truncate the file
            ftruncate($h,0);
            
            // Serializing along with the TTL
            $data   = serialize( array( time()+$ttl, $data ) );
            $retVal = fwrite( $h, $data ) === false ? false : true;
            fclose($h);
        }
        return $retVal;
    }

    // The function to fetch data returns false on failure
    function fetch($key) 
    {
        return $this->load( $this->getFileName($key) );
    }

    function remove( $key ) 
    {
        $filename = $this->getFileName($key);
        if ( file_exists($filename) ) 
        {
            return unlink($filename);
        } 
        else 
        {
            return false;
        }
    }
    
    function purgeAll()
    {
        foreach(glob($this->pathToCache. "/cache-*") as $file) 
        {
            // Will be deleted if it has expire
            $this->load($file);
        }
    }
    
    function deleteAll()
    {
        foreach(glob($this->pathToCache. "/cache-*") as $file) 
        {
            unlink($file);
        }
    }
    
    private function load($filename) 
    {
        if ( file_exists($filename))
        {
            $h = fopen($filename,'r');
            
            if (!$h) return false;
            
            // Getting a shared lock 
            flock($h,LOCK_SH);
            
            $data = file_get_contents($filename);
            fclose($h);
            
            $data = @unserialize($data);
            if (!$data) 
            {
                // If unserializing somehow didn't work out, we'll delete the file
                unlink($filename);
                return false;
            }
            
            if ( time() > $data[0] ) 
            {
                // Unlinking when the file was expired
                unlink($filename);
                return false;
            }
            return $data[1];
        }
        return false;
    }
    
    
    private function getFileName($key) 
    {
        return rtrim( $this->pathToCache, '/') . '/cache-' . md5($key);
    }
}