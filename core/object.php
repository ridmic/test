<?php
namespace Ridmic\Core;

class Object
{
    static       $defLevel     = 1; 
    static       $defShowClass = false;
    
    const        DBG_ALWAYS    = 0;
    const        DBG_DEBUG     = 1;
    const        DBG_TRACE     = 2;
    
    function __construct()
    {
      $this->traceFunction();
    }
    
    function __destruct()
    {
      $this->traceFunction();
    }
    
    static function defDebugLevel( $level )
    {
      self::$defLevel = intval($level );
    }
    static function defShowClass( $b=true )
    {
      self::$defShowClass = $b === true;
    }
    
    public function write( $msg ) { $this->_write($msg, self::DBG_ALWAYS); }
    public function debug( $msg ) { $this->_write($msg, self::DBG_DEBUG); }
    public function trace( $msg ) { $this->_write($msg, self::DBG_TRACE); }
    public function _write( $msg, $level=1 )
    {
      if ( $level <= self::$defLevel )
      {
          if ( self::$defShowClass )
            print "<b>[".get_class($this)."]</b>: $msg<br>\n";
          else
            print "$msg<br>";
      }
    } 
    
    public function traceFunction()
    {
      $b = debug_backtrace(false,2);
      $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
      $v = count($b) >= 2 ? $b[1]["args"] : array();
          
      array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
      $this->trace( get_class($this)."::$f(".implode(', ', $v).")" );
    }
}

?>