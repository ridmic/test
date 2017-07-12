<?php
namespace DryMile\Core;

include_once "Utils/Object.php";
include_once "Debug.php";
include_once "ResponseCode.php";

class View extends Utils\Object
{
    protected $app          = null;
    protected $useFolders   = false;
 	protected $assigns		= array();
    protected $language     = [];

    public function __construct( App $app )
    {
        parent::__construct();
    
        $this->app = $app;
    }

	public function assign( $sVarName, $sVal )
	{
		$sVal     = htmlentities($sVal, ENT_QUOTES, 'UTF-8');
		$sVarName = 'v'.strtoupper($sVarName);
		$this->assigns[$sVarName] = $sVal;
	}
    
	public function assignObject( $sVarName, $object )
	{
	    if ( is_object($object))
	    {
		    $sVarName = 'the'.strtoupper($sVarName);
		    $this->assigns[$sVarName] = $object;
	    }
	}

	public function assignCallable( $sVarName, $call )
	{
	    if ( is_callable($call))
	    {
		    $sVarName = 'vf'.strtoupper($sVarName);
		    $this->assigns[$sVarName] = $call;
	    }
	}

    public function render( $name )
    {
        $contents = $this->fill( $name );

        if ( $contents === false ) 
        {
            return $this->makeResponse(ResponseCode::CODE_INTERNALERROR);
        }

        return $this->makeResponse( ResponseCode::CODE_OK, $contents );
    }

    public function fill( $name )
    {
        $name           = self::toClassName($name);
        $viewFile       = $this->useFolders ? $name.'/'.$name : $name;
        $viewFile       = $this->app->pathToView( $viewFile );
        Debug::debug( "View File: %s",$viewFile );
        
        if ( !file_exists( $viewFile ) ) 
        {
            return false;
        }
        // Give our view access to language
        if ( !array_key_exists( 'vfLANG', $this->assigns ) )
            $this->assignCallable( 'lang', array($this, 'L') );
        
		// Make our variables available to the templates
		extract ( $this->assigns );
		ob_start();

        include_once $viewFile;
        
        $contents = ob_get_contents();
        
        ob_end_clean();

        return $contents;
    }

    public function L( $index, $args=[] )
    {
        $text = $index;
        if ( array_key_exists($index, $this->language) )
            $text = $this->language[$index];

        // Allow replacable params
        $count = 1;
        $args  = is_array( $args ) ? $args : [ $args ];
        foreach ( $args as $arg )
        {
            $repl = '%'.$count;
            $text = str_replace( $repl, $arg, $text );
            $count++;
        }
        return $text;
    }

    public function loadLanguage( $name )
    {
        // Pull in any requested language
        $name     = $this->useFolders ? $name.'/'.$name : $name;
        $langFile = $this->app->pathToLanguage( $name );
        Debug::debug( "Language File: %s",$langFile );
        if ( file_exists( $langFile ) ) 
        {
            $lang = array();
            include $langFile;

            // Copy the language to our own
            $this->language = array_merge( $this->language, $lang );
            $lang = array();
            return true;
        }
        return false;
    }


    protected function makeResponse( $code, $contents = [] )
    {
        $response = new ResponseCode( $code );
        $response->loadResponse( $contents );
        return $response;
    }

    
}