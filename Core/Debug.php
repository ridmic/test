<?php namespace DryMile\Core;

require_once "Utils/Logger.php";

class Debug
{
    static protected $level         = self::DBG_WRITE; 
    static protected $showDate      = false;
    static protected $showDebug     = true;
    static protected $curIndent     = 0;
    static protected $loggers       = [];
    static protected $buffer        = [];
    
    const DBG_WRITE                 = 0;
    const DBG_DEBUG                 = 1;
    const DBG_TRACE                 = 2;

    static function level( $level )
    {
        self::$level = intval($level );
    }
    
    static function setLogger( $logger )
    {
        if ( $logger instanceof Utils\aLogger )
        {
            self::$loggers = [ $logger ];   
        }
    }

    static function addLogger( $logger )
    {
        if ( $logger instanceof Utils\aLogger )
        {
            self::$loggers[] = $logger;   
        }
    }

    static function write( $msg )   { self::_writeWithArgs( $msg, self::DBG_WRITE );  } 
    static function debug( $msg )   { self::_writeWithArgs( $msg, self::DBG_DEBUG );  }
    static function trace( $msg )   { self::_writeWithArgs( $msg, self::DBG_TRACE );  }
    
    static function traceEnterFunc()
    {
        if ( empty(self::$loggers) || self::DBG_TRACE <= self::$level )
        {
            $b = debug_backtrace(false,2);
            $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
            $v = count($b) >= 2 ? $b[1]["args"] : array();
            $c = count($b) >= 2 ? $b[1]["class"] : '';
            
            $file   = count($b) >= 2 ? basename($b[1]['file']) : '-unknown-';
            $line   = count($b) >= 2 ? $b[1]['line'] : '-unknown-';
            $detail = "{ $file @ $line }";
              
            self::_write( "FUNC >>: $c::$f(...) : $detail", self::DBG_TRACE );
            
            self::$curIndent += 2;
        }
    }
        
    public function traceLeaveFunc( $vv = '' )
    {
        if ( empty(self::$loggers) || self::DBG_TRACE <= self::$level )
        {
            self::$curIndent = max( 0, self::$curIndent - 2);
            
            $b = debug_backtrace(false,2);
            $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
            $v = count($b) >= 2 ? $b[1]["args"] : array();
            $c = count($b) >= 2 ? $b[1]["class"] : '';
            
            $file   = count($b) >= 2 ? basename($b[1]['file']) : '-unknown-';
            $line   = count($b) >= 2 ? $b[1]['line'] : '-unknown-';
            $detail = "{ $file @ $line }";
            
            self::_write( "FUNC <<: $c::$f( ... ) => retval($vv) : $detail", self::DBG_TRACE );
        }
    }
    
    static protected function _writeWithArgs( $msg, $level ) 
    { 
        if ( empty(self::$loggers) || $level <= self::$level )
        {
            $b = debug_backtrace(false,2);
            $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
            $v = count($b) >= 2 ? $b[1]["args"] : array();
    
            $file   = count($b) >= 2 ? basename($b[1]['file']) : '-unknown-';
            $line   = count($b) >= 2 ? $b[0]['line'] : '-unknown-';
            $detail = "{ $file @ $line }";
              
            array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
            array_shift($v);
            $msg = vsprintf( $msg, $v );
            
            self::_write( "$msg : $detail", $level );
        }
    }

    static protected function _write( $msg, $level )
    {
        $sp = str_repeat( '.', self::$curIndent );
        
        if ( empty(self::$loggers) )
        {
            // Buffer whilst we await our logger to be assigned
            if ( count(self::$buffer) < 1000 )
                self::$buffer[] = [$level, "$sp$msg"];
        }
        else
        {
            // Output any buffered content
            if ( count(self::$buffer) )
            {
                foreach ( self::$buffer as $buff )
                    self::output( $buff[1], $buff[0] );
                self::$buffer = [];            
            }
            // Output it
            self::output( "$sp$msg", $level );
        }
    } 

    static protected function output( $msg, $level )
    {
        if ( $level <= self::$level )
        {
            // output the message
            foreach ( self::$loggers as $logger )
                $logger->write( $msg );    
        }
    }
}
