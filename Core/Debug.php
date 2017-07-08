<?php
namespace DryMile\Core;

class Debug
{
    static protected  $level      = 1; 
    static protected  $showDate   = false;
    static protected  $showDebug  = true;
    static protected  $curIndent  = 0;
    
    const        DBG_ALWAYS       = 0;
    const        DBG_DEBUG        = 1;
    const        DBG_TRACE        = 2;
    
    function __construct()
    {
    }
    
    function __destruct()
    {
    }
    
    static function level( $level )
    {
      self::$level = intval($level );
    }
    static function showDateTime( $b=true )
    {
      self::$showDate = $b === true;
    }
    
    static function _debug( $msg ) { self::$_write($msg, self::DBG_TRACE); }
    static function _trace( $msg ) { self::$_write($msg, self::DBG_TRACE); }
    static function _write( $msg, $level=1 )
    {
      if ( $level <= self::$level )
      {
          $date = new \DateTime();
          $dt   = $date->format('d/m/Y H:i:s');
          $sp   = str_repeat( '. ', self::$curIndent );
          if ( self::$showDebug === true )
          {
              if ( self::$showDate )
                print "[$dt] $sp $msg<br>\n";
              else
                print "$sp $msg<br>";
          }
       }
    } 
    
    static function write( $msg ) 
    { 
      if ( self::DBG_ALWAYS <= self::$level )
      {
        $b = debug_backtrace(false,1);
        $f = count($b) >= 1 ? $b[0]['function'] : '-unknown-';
        $v = count($b) >= 1 ? $b[0]["args"] : array();

        $file   = count($b) >= 1 ? basename($b[0]['file']) : '-unknown-';
        $line   = count($b) >= 1 ? $b[0]['line'] : '-unknown-';
        $detail = "{ $file @ $line }";
          
        array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
        array_shift($v);
        $msg = vsprintf( $msg, $v );
        self::_write( "$msg : $detail", DBG_ALWAYS );
      }
    }

    static function debug( $msg )
    {
      if ( self::DBG_DEBUG <= self::$level )
      {
        $b = debug_backtrace(false,1);
        $f = count($b) >= 1 ? $b[0]['function'] : '-unknown-';
        $v = count($b) >= 1 ? $b[0]["args"] : array();

        $file   = count($b) >= 1 ? basename($b[0]['file']) : '-unknown-';
        $line   = count($b) >= 1 ? $b[0]['line'] : '-unknown-';
        $detail = "{ $file @ $line }";
          
        array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
        array_shift($v);
        $msg = vsprintf( $msg, $v );
        self::_write( "$msg : $detail", DBG_DEBUG );
      }
    }

    static function traceEnterFunc()
    {
      if ( self::DBG_TRACE <= self::$level )
      {
        $b = debug_backtrace(false,2);
        $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
        $v = count($b) >= 2 ? $b[1]["args"] : array();

        $file   = count($b) >= 2 ? basename($b[1]['file']) : '-unknown-';
        $line   = count($b) >= 2 ? $b[1]['line'] : '-unknown-';
        $detail = "{ $file @ $line }";
          
        array_walk( $v, function(&$value, $key) { $value = print_r( $value, true); } );
        self::_write( "FUNC >>: ".get_class($this)."::$f(".implode(', ', $v).") : $detail", DBG_TRACE );

        self::$curIndent += 2;
      }
    }
        
    public function traceLeaveFunc( $vv = '' )
    {
      if ( self::DBG_TRACE <= self::$level )
      {
        self::$curIndent = max( 0, self::$curIndent - 2);
        
        $b = debug_backtrace(false,2);
        $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
        $v = count($b) >= 2 ? $b[1]["args"] : array();
          
        $file   = count($b) >= 2 ? basename($b[1]['file']) : '-unknown-';
        $line   = count($b) >= 2 ? $b[1]['line'] : '-unknown-';
        $detail = "{ $file @ $line }";
          
        array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
        self::_write( "FUNC <<: ".get_class($this)."::$f(".implode(', ', $v).") => retval(".print_r($vv, true).") : $detail", DBG_TRACE );
      }
    }
}
