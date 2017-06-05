<?php
namespace Ridmic\Core;

class Object
{
    static            $defLevel    = 1; 
    static            $defShowDate = false;
    static protected  $curIndent   = 0;
    
    const        DBG_ALWAYS    = 0;
    const        DBG_DEBUG     = 1;
    const        DBG_TRACE     = 2;
    
    function __construct()
    {
    }
    
    function __destruct()
    {
    }
    
    static function defDebugLevel( $level )
    {
      self::$defLevel = intval($level );
    }
    static function defShowDateTime( $b=true )
    {
      self::$defShowDate = $b === true;
    }
    
    public function write( $msg ) { $this->_write($msg, self::DBG_ALWAYS); }
    public function _debug( $msg ) { $this->_write($msg, self::DBG_TRACE); }
    public function _trace( $msg ) { $this->_write($msg, self::DBG_TRACE); }
    public function _write( $msg, $level=1 )
    {
      if ( $level <= self::$defLevel )
      {
          $date = new \DateTime();
          $dt   = $date->format('d/m/Y H:i:s');
          $sp   = str_repeat( '. ', self::$curIndent );
          if ( self::$defShowDate )
            print "[$dt] $sp $msg<br>\n";
          else
            print "$sp $msg<br>";
      }
    } 
    
    public function debug( $msg )
    {
      if ( self::DBG_DEBUG <= self::$defLevel )
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
        $this->_write( "$msg : $detail", DBG_DEBUG );
      }
    }

    public function traceEnterFunc()
    {
      if ( self::DBG_TRACE <= self::$defLevel )
      {
        $b = debug_backtrace(false,2);
        $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
        $v = count($b) >= 2 ? $b[1]["args"] : array();

        $file   = count($b) >= 2 ? basename($b[1]['file']) : '-unknown-';
        $line   = count($b) >= 2 ? $b[1]['line'] : '-unknown-';
        $detail = "{ $file @ $line }";
          
        array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
        $this->_write( "FUNC >>: ".get_class($this)."::$f(".implode(', ', $v).") : $detail", DBG_TRACE );

        self::$curIndent += 2;
      }
    }
        
    public function traceLeaveFunc( $vv = '' )
    {
      if ( self::DBG_TRACE <= self::$defLevel )
      {
        self::$curIndent = max( 0, self::$curIndent - 2);
        
        $b = debug_backtrace(false,2);
        $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
        $v = count($b) >= 2 ? $b[1]["args"] : array();
          
        $file   = count($b) >= 2 ? basename($b[1]['file']) : '-unknown-';
        $line   = count($b) >= 2 ? $b[1]['line'] : '-unknown-';
        $detail = "{ $file @ $line }";
          
        array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
        $this->_write( "FUNC <<: ".get_class($this)."::$f(".implode(', ', $v).") => retval(".print_r($vv, true).") : $detail", DBG_TRACE );
      }
    }

    public function __toString()
    {
      return "Object(".get_class($this).")";
    }
}
