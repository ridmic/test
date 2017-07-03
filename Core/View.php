<?php
namespace Ridmic\Core;

include_once "Debug.php";
include_once "Object.php";
include_once "ResponseCode.php";

class View extends Object
{
    protected $app          = null;
    protected $useFolders   = false;
 	protected $assigns		= array();

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
        // Give our view access to language
        //$this->assignCallable( 'lang', array($this->controller, 'L') );
        
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
        //$this->assignCallable( 'lang', array($this->controller, 'L') );
        
		// Make our variables available to the templates
		extract ( $this->assigns );
		ob_start();

        include_once $viewFile;
        
        $contents = ob_get_contents();
        
        ob_end_clean();

        return $contents;
    }

    protected function makeResponse( $code, $contents = [] )
    {
        $response = new ResponseCode( $code );
        $response->loadResponse( $contents );
        return $response;
    }
    
}