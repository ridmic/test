<?php

// Pull in our framework config
require __DIR__ . "/../Core/Config.php";

use DryMile\Core        as Core;
use DryMile\Core\Utils  as Utils;

Core\Debug::level( Core\Debug::DBG_TRACE );
Core\Debug::setLogger( new Utils\HtmlLogger(true) );

// ========================================================
// We can create simple closure based app
// ========================================================
//$myApp = Core\AppFactory::build( 'test' );
//$myApp->router()->route()->add( 'GET', '/{:any}', function () { echo 'HELLO!'; } );        
//$myApp->run();
// ========================================================

// ========================================================
// We can create simple function based app
// ========================================================
//function HelloWorld() { echo 'HELLO!'; };
//$myApp = Core\AppFactory::build( 'test' );
//$myApp->router()->route()->add( 'GET', '/{:any}', 'HelloWorld' );        
//$myApp->run();
// ========================================================

// ========================================================
// We can create simple class based app
// ========================================================
//class myClass { public function HelloWorld() { echo 'HELLO!'; } };
//$myApp = Core\AppFactory::build( 'test' );
//$myApp->router()->route()->add( 'GET', '/{:any}', 'myClass@HelloWorld' );        
//$myApp->run();
// ========================================================

// ========================================================
// We can create MVC based app ( handles: /test/user/{:id} )
// ========================================================
//$myApp = Core\AppFactory::buildMvc( 'test' );
//$myApp->run();
// ========================================================

//$myApp = Core\AppFactory::buildMvc( 'roll_it', true, Core\Responder::TYPE_HTML );

//$myApp = Core\AppFactory::buildMvc( 'mike', false, Core\Responder::TYPE_HTML );

//$myApp->responder()->respond( $myApp->run() );

// TODO:
// Add API Protection (oauth)

// TODO:
// Unit Testing:
// add tests for utils


include CORE_DIR . "Utils/Header.php";

$header = new Utils\Header();

foreach(
  array(
    'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
    'text/*;q=0.3, text/html;q=0.8, application/xhtml+xml;q=0.7, */*;q=0.2',
    'text/*;q=0.3, text/html;q=0.7, */*;q=0.8',
    'text/*, application/xhtml+xml',
    'text/html, application/xhtml+xml'
  ) as $testheader) 
  {  
    $_SERVER['HTTP_ACCEPT'] = $testheader;
    $accepting = Array ('application/xhtml+xml', 'text/html');

    echo "TEST : $testheader<br>\n";
    echo "ACCEPT : ".implode(' or ' , $accepting)."<br>\n";
    var_dump( $header->getBestSupportedMimeType() );
    echo "FOUND: ". $header->getBestSupportedMimeType($accepting)."<br>\n";
  }