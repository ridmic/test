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

	public function assign( $varName, $val )
	{
		$val      = htmlentities($val, ENT_QUOTES, 'UTF-8');
		$varName = 'v'.strtoupper($varName);
		$this->assigns[$varName] = $val;
	}
    
    public function assignTemplate( $varName, $template )
    {
		$varName = 'v'.strtoupper($varName);
		$this->assigns[$varName] = $this->fill( $template );
    }
    
	public function assignCallable( $varName, $call )
	{
	    if ( is_callable($call))
	    {
		    $varName = 'vf'.strtoupper($varName);
		    $this->assigns[$varName] = $call;
	    }
	}

	public function assignObject( $varName, $call )
	{
	    if ( is_object($call))
	    {
		    $varName = 'the'.strtoupper($varName);
		    $this->assigns[$varName] = $call;
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

    public function load( $name )
    {
        echo $this->fill( $name );    
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