<?php namespace DryMile\Core\Utils;

class LeakyBucket
{
    protected   $capacity       = 0;        // Total capacity
    protected   $interval       = 0;        // seconds : 60 = 1 minute, 3600 = 1 hour
    protected   $rate           = 0;        // rate per interval
    protected   $volume         = 0;
    protected   $burstRate      = 0;        // Set this to control the burst rate (0=max)
    protected   $stopLeak       = false;
    protected   $overfill       = false;
    protected   $useMicrotime   = true;
    protected   $lastRequest    = 0;

    public function __construct( $capacity = 120, $interval = 60 )
    {
        $this->capacity     = $capacity;
        $this->interval     = $interval;
        $this->rate         = $this->capacity / $this->interval;
        $this->volume       = 0;
        $this->lastRequest  = time();
        $this->maxBurst     = 0;
    }
    
    public function setMaxBurstRate( $rate )    { $this->burstRate = max( 0, min( $rate, $this->capacity ) );  }
    public function setMaxCapacity( $capacity ) { $this->capacity  = is_int($capacity) ? $capacity : 0; }
    public function setInterval( $interval )    { $this->interval  = is_int($interval) ? $interval : 0; }
    public function stopLeak( $bool = true )    { $this->stopLeak  = is_bool($bool) ? $bool : false; }
    public function overfill( $bool = true )    { $this->overfill  = is_bool($bool) ? $bool : false; }
    public function useMicrotime( $bool = true ){ $this->useMicrotime = is_bool($bool) ? $bool : false; }
    
    public function getlastChecked()            { return $this->fetchLastChecked(); }

    // ------------------------------------------------------------------------

    // Attempt to add some drips to the bucket (returns true if we added else false)
    public function add( $drips = 1 )
    {
        if ( $this->checkCapacity($drips) )
        {
            $this->dripIn( $drips );
            return true;
        }
        return false;
    }
    
    // Load the state of the bucket
    public function load( $volume, $lastChecked )
    {
        // Set the start volume
        $this->storeVolume( 0 );
        if ( is_int($volume) && $volume > 0 )    
        {
            $this->dripIn( $drips );
        }
        // Set the last request time
        $this->storeLastChecked( $this->getTime() );
        if ( is_int($lastChecked) && $lastChecked > 0 )    
        {
            $this->storeLastChecked( $lastChecked );
        }
    }

    // ------------------------------------------------------------------------

    // Fill the bucket
    public function fillBucket()                { $this->dripIn( $this->capacity ); }
    // Empty the bucket
    public function emptyBucket()               { $this->dripOut( $this->capacity ); }
    // Add some drips in the bucket
    public function dripIn( $drips = 1 )        { $this->addDrips( $drips ); }
    // Release some drips from the bucket
    public function dripOut( $drips = 1 )       { $this->addDrips( -1 * $drips ); }
    // Do we have capacity for the requested number of drips?
    public function checkCapacity( $drips = 1 ) { return $this->getCapacity() >= $drips; }
    // What is our current capacity?
    public function getCapacity()               
    { 
        $this->updateVolume(); return max( 0, floor($this->capacity - $this->fetchVolume()) ); 
    }
    // What is our current volume?
    public function getVolume()                 { $this->updateVolume(); return $this->fetchVolume(); }
    
    // ------------------------------------------------------------------------
    
    // Add (or remove) drips to the bucket
    protected function addDrips( $drips = 1 )
    {
        if ( is_int($drips) )
        {
            $volume  = $this->fetchVolume();
            $volume += $drips;
            $volume  = $this->overfill ? $volume : min( $this->capacity, $volume );
            $volume  = $this->burstRate > 0 ? max( $volume, $this->capacity - $this->burstRate ) : $volume;
            $volume  = max( 0, $volume );
            $this->storeVolume($volume);
        }
    }
    
    // Update the bucket based on elapsed time 
    protected function updateVolume()
    {
        $diff = $this->getTime() - $this->fetchLastChecked();
        if ( !$this->stopLeak && $diff > 0 )
        {
            $volume  = $this->fetchVolume();
            $volume -= $diff * $this->rate;
            $volume  = max( 0, $volume );
            $volume  = $this->burstRate > 0 ? max( $volume, $this->capacity - $this->burstRate ) : $volume;
            $this->storeVolume($volume);
            
            $this->storeLastChecked( $this->getTime() );
        }
    }
    
    // Return the time for our interval calculations (use microtime by default)
    protected function getTime()
    {
        return $this->useMicrotime ? microtime(true) : time();
    }
    
    // Override these in a sub class to add persistence
    protected function fetchVolume()          { return $this->volume; }
    protected function storeVolume($v)        { $this->volume = $v; }
    protected function fetchLastChecked()     { return $this->lastChecked; }
    protected function storeLastChecked($v)   { $this->lastChecked = $v; }
}
