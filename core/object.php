<?php

class xObject
{
    static       $defLevel   = 1; 
    static       $defMode    = 'HTML';
    protected    $dbgLevel   = 0;
    
    const        DBG_ALWAYS  = 0;
    const        DBG_DEBUG   = 1;
    const        DBG_TRACE   = 2;
    
    function __construct()
    {
        $this->dbgLevel = self::$defLevel;
    }
    static function defDebugLevel( $level )
    {
        self::$defLevel = intval($level );
    }
    
    public function write( $msg ) { $this->_write($msg, self::DBG_ALWAYS); }
    public function debug( $msg ) { $this->_write($msg, self::DBG_DEBUG); }
    public function trace( $msg ) { $this->_write($msg, self::DBG_TRACE); }
    public function _write( $msg, $level=1 )
    {
        if ( $level <= $this->dbgLevel )
        {
            switch ( self::$defMode )
            {
                case 'HTML':
                    print "<b>DBG".$level."[<i>".__CLASS__."</i>]</b>: $msg<br>\n";    
                    break;
                default:
                    print "DBG".$level."[".__CLASS__."]: $msg\n";
                    break;
            }
        }
    }  
    public function setDebugLevel( $level=1 )
    {
        $this->dbgLevel = intval($level);
    }
}

?>