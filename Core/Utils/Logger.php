<?php namespace DryMile\Core\Utils;

abstract class aLogger
{
    const ERROR_LEVEL       = 255;
    const WRITE             = 0;
    const DEBUG             = 1;
    const NOTICE            = 2;
    const WARNING           = 4;
    const ERROR             = 8;

    const DIV_H1            = '#';
    const DIV_H2            = '=';
    const DIV_H3            = '-';
    const DIV_HX            = ' ';

    const BOX_CORNER        = '+';
    const BOX_HEADER        = '-';
    const BOX_ROW           = '|';

    const BOX_ROW_LEFT      = STR_PAD_RIGHT;
    const BOX_ROW_CENTER    = STR_PAD_BOTH;
    const BOX_ROW_RIGHT     = STR_PAD_LEFT;

    protected $timestamp    = false;
    protected $pageWidth    = 80;
    protected $startUp      = true;

    public function __construct( $startUp = false, $timestamp = false )  
    {
        $this->startUp = $startUp === true ? true : false;
        $this->timestamp($timestamp);
        if ( $this->startUp )
        {
            $date = new \DateTime();
            $time = "[".$date->format('d/m/Y H:i:s')."]";
            $this->writeHeader_H1("LOG START $time");
        }
    }
    public function __destruct()
    {
        if ( $this->startUp )
        {
            $date = new \DateTime();
            $time = "[".$date->format('d/m/Y H:i:s')."]";
            $this->writeHeader_H1("LOG END $time");
        }
    }
    
    public function timestamp( $b )             { $this->timestamp = $b === true ? true : false; }
    public function pageWidth()                 { return $this->pageWidth; }
    public function setPageWidth( $w )          { $this->pageWidth = max( 10, intval($w)); }
    
    public function writeHeader_H1( $str )      { $this->writeDivider( " $str ", self::DIV_H1); }
    public function writeHeader_H2( $str )      { $this->writeDivider( " $str ", self::DIV_H2); }
    public function writeHeader_H3( $str )      { $this->writeDivider( " $str ", self::DIV_H3); }
    public function writeHeader_HX( $str )      { $this->writeDivider( " $str ", self::DIV_HX); }

    public function writeDivider_H1()           { $this->writeDivider( '', self::DIV_H1); }
    public function writeDivider_H2()           { $this->writeDivider( '', self::DIV_H2); }
    public function writeDivider_H3()           { $this->writeDivider( '', self::DIV_H3); }
    public function writeDivider( $str = '', $pad )
    {
        $this->write( str_pad( $str, $this->pageWidth, $pad, STR_PAD_BOTH ), 0 );
    }
    
    public function writeDividerRow_H1( $str )  { $this->writeDividerRow( $str, self::DIV_H1); }
    public function writeDividerRow_H2( $str )  { $this->writeDividerRow( $str, self::DIV_H2); }
    public function writeDividerRow_H3( $str )  { $this->writeDividerRow( $str, self::DIV_H3); }
    public function writeDividerRow_HX( $str )  { $this->writeDividerRow( $str, self::DIV_HX); }
    public function writeDividerRow( $str, $pad, $align= self::BOX_ROW_CENTER )
    {
        $this->write( $pad.' ' . str_pad( "$str", $this->pageWidth - 2 -(2*strlen($pad)), ' ', $align ) . ' '. $pad, 0 );
    }

    public function writeHeading_H1( $str )     { $this->writeHeading( $str, self::DIV_H1); }
    public function writeHeading_H2( $str )     { $this->writeHeading( $str, self::DIV_H2); }
    public function writeHeading_H3( $str )     { $this->writeHeading( $str, self::DIV_H3); }
    public function writeHeading( $str, $pad )
    {
        $this->writeDivider( '', $pad );
        $this->writeDividerRow( $str, $pad );
        $this->writeDivider( '', $pad );
    }

    public function writeBox( $str = '' )
    {
        $this->writeBoxHeader();
        $this->writeBoxRow( $str );
        $this->writeBoxFooter();
    }
    
    public function writeBoxHeader( $str = '' )
    {
        $this->write( self::BOX_CORNER . str_pad( "$str", $this->pageWidth - 2,self::BOX_HEADER, STR_PAD_BOTH ) . self::BOX_CORNER, 0 );
    }
    public function writeBoxRow( $str = '', $align= self::BOX_ROW_CENTER )
    {
        $this->write( self::BOX_ROW.' ' . str_pad( "$str", $this->pageWidth - 4, ' ', $align ) . ' '. self::BOX_ROW, 0 );
    }
    public function writeBoxFooter( $str = '' )
    {
        $this->write( self::BOX_CORNER . str_pad( "$str", $this->pageWidth - 2,self::BOX_HEADER, STR_PAD_BOTH ) . self::BOX_CORNER, 0 );
    }

    public function writeLn()   
    {
        $this->write('');    
    }
    
    public function write( $message, $level = self::WRITE )
    {
        $date     = new \DateTime();
        $preamble = $this->timestamp ? "[".$date->format('d/m/Y H:i:s')."] " : '';

        switch($level)
        {
            case self::NOTICE:
                $preamble = sprintf("%s{N} :", $preamble);
                break;
            case self::WARNING:
                $preamble = sprintf("%s{W} :", $preamble);
                break;
            case self::ERROR:
                $preamble = sprintf("%s{E} :", $preamble);
                break;
            case self::DEBUG:
                $preamble = sprintf("%s{D} :", $preamble);
                break;
            default:
                break;
        }
        $message = sprintf("%s%s",  $preamble, $message);
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
        parent::_write( nl2br($message)."<br />" );
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
            
        parent::__construct( true, true );
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
