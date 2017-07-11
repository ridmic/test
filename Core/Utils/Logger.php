<?php namespace DryMile\Core\Utils;


abstract class aLogger
{
    const ERROR_LEVEL   = 255;
    const WRITE         = 0;
    const DEBUG         = 1;
    const NOTICE        = 2;
    const WARNING       = 4;
    const ERROR         = 8;

    public function __construct()  
    {
        $this->write("===================== STARTING =====================", 0);
     }
    public function __destruct()
    {
        $this->write("====================== ENDING ======================", 0);
    }
    
    public function write( $message, $level = self::WRITE )
    {
        $date     = new \DateTime();
        $preamble = $date->format('d/m/Y H:i:s');

        switch($level)
        {
            case self::NOTICE:
                $preamble = sprintf("[%s] {N} :", $preamble);
                break;
            case self::WARNING:
                $preamble = sprintf("[%s] {W} :", $preamble);
                break;
            case self::ERROR:
                $preamble = sprintf("[%s] {E} :", $preamble);
                break;
            case self::DEBUG:
                $preamble = sprintf("[%s] {D} :", $preamble);
                break;
            default:
                $preamble = sprintf("[%s]", $preamble);
                break;
        }
        $message = sprintf("%s %s",  $preamble, $message);
        $this->_write( $message );
    }
    
    // Override this to actually 'write something out'
    protected function _write( $message )    {  }
}
 
class NullLogger extends aLogger
{
    protected function _write( $message )
    {
    }    
}

class ConsoleLogger extends aLogger
{
    protected function _write( $message )
    {
        echo $message."\n";
    }    
}

class HtmlLogger extends ConsoleLogger
{
    protected function _write( $message )
    {
        parent::_write( $message."<br />" );
    }    
}
 
class FileLogger extends aLogger
{
    const WRAP_NEVER    = 0;
    const WRAP_DAILY    = 1;
    const WRAP_WEEKLY   = 2;
    const WRAP_MONTHLY  = 4;
    const WRAP_YEARLY   = 8;
    
    protected $logFile  = null;
    protected $wrapFile = self::WRAP_DAILY;
    
    public function __construct( $logFile, $wrapFile = self::WRAP_NEVER )  
    {
        $this->logfile = $this->makeFilename( $logFile, $wrapFile );
        // If we are not wrapping, remove the file before we start
        if ( $wrapFile == self::WRAP_NEVER )
            @unlink( $this->logfile );
            
        parent::__construct();
    }

    public function setLogFile( $logFile )  { $this->logfile = $logFile; }

    public function makeFilename( $path, $wrapFile = self::WRAP_NEVER )
    {
        $dir        = dirname( $path );
        $filename   = basename( $path );
        $bits       = explode( '.' , $filename );
        $ext        = count($bits) >= 2 ? array_pop( $bits ) : 'log';
        $filename   = implode( '.', $bits );
        $date       = new \DateTime();
        
        switch ( $wrapFile )
        {
            case self::WRAP_DAILY:
                $wrapper  = $date->format('Y-m-d');
                $filename = $filename . '-' . $wrapper;
                break;
            case self::WRAP_WEEKLY:
                $wrapper  = $date->format('Y-')."W".$date->format('W');
                $filename = $filename . '-' . $wrapper;
                break;
            case self::WRAP_MONTHLY:
                $wrapper  = $date->format('Y-m');
                $filename = $filename . '-' . $wrapper;
                break;
            case self::WRAP_YEARLY:
                $wrapper  = $date->format('Y');
                $filename = $filename . '-' . $wrapper;
                break;
            default:
                break;
        }
        return $dir == '.' ? "$filename.$ext" : "$dir/$filename.$ext";
    }
    
    protected function _write( $message )
    {
        if ( ! is_null($this->logfile) )
        {
            file_put_contents($this->logfile, $message."\n" , FILE_APPEND | LOCK_EX);
        }
    }
}
