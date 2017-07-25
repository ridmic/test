<?php namespace DryMile\Core\Utils;

class Cache
{
    protected $cachePath    = null;
    protected $cacheTimeout = 3600;
    
    public function __constructor( $cacheTimeout = 3600 )
    {
        $this->cacheTimeout = $cacheTimeout;    
    }
    
    public function store( $key, $data, $ttl = 3600 )
    {
        $cache      = $this->makeCachePath( $key );
        $lifetime   = time() + $ttl;
        $serialized = serialize($data);
        $result     = file_put_contents($cache, $lifetime . PHP_EOL . $serialized);
        if ( $result === false )
            return false;
        return true;
    }
    
    public function retrieve( $key )
    {
        // Does it exist
        $cache = $this->makeCachePath( $key );
        if ( !is_file($cache) || !is_readable($cache)) 
            return false;

        // Has the file timed out?
	    if ( $this->cacheTimeout > 0 && filemtime($cache) + $this->cacheTimeout < time() )
	    {
            @unlink($cache);
	        return false;
	    }

        // Read in the cache object
        $lines    = file($cache);
        $lifetime = array_shift($lines);
        $lifetime = (int) trim($lifetime);
        
        // Has the cached object timed out?
        if ($lifetime !== 0 && $lifetime < time() ) 
        {
            @unlink($cache);
            return false;
        }
        $serialized = join('', $lines);
        $data       = unserialize($serialized);
        return $data;
    }
    
    public function remove( $key )
    {
        $cache = $this->makeCachePath( $key );
        return unlink($cache);
    }
    
    public function clearCache()
    {
        $files = glob( rtrim( $this->cachePath, '/').'/*.cache' ); // get all file names
        foreach($files as $file)
        {   // iterate files
            if( is_file($file) )
            unlink($file); // delete file
        }        
    }
    
    public function setCachePath( $path )
    {
        $this->cachePath = rtrim( $path, '/' );
    }
    
    // Protected
    public function makeCachePath( $key )
    {
        return rtrim( $this->cachePath, '/').'/'.$this->makeFilename( $key );    
    }
    
    protected function makeFilename( $key )
    {
        return md5($key).'.cache';    
    }
}
