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
      $this->traceEnterFunc();
      
      $this->traceLeaveFunc();
    }
    
    function __destruct()
    {
      $this->traceEnterFunc();
      
      $this->traceLeaveFunc();
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
    public function debug( $msg ) { $this->_write($msg, self::DBG_DEBUG); }
    public function trace( $msg ) { $this->_write($msg, self::DBG_TRACE); }
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
    
    public function traceEnterFunc()
    {
      if ( self::$defLevel == self::DBG_TRACE )
      {
        $b = debug_backtrace(false,2);
        $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
        $v = count($b) >= 2 ? $b[1]["args"] : array();
          
        array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
        $this->trace( "ENTER: ".get_class($this)."::$f(".implode(', ', $v).")" );

        self::$curIndent += 2;
      }
    }
        
    public function traceLeaveFunc( $vv = '' )
    {
      if ( self::$defLevel == self::DBG_TRACE )
      {
        self::$curIndent = max( 0, self::$curIndent - 2);
        
        $b = debug_backtrace(false,2);
        $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';
        $v = count($b) >= 2 ? $b[1]["args"] : array();
          
        array_walk( $v, function(&$value, $key) { $value = print_r($value, true); } );
        $this->trace( "LEAVE: ".get_class($this)."::$f(".implode(', ', $v).") => retval(".print_r($vv, true).")" );
      }
    }

    public function __toString()
    {
      return get_class($this)." Object()";
    }
}

?>