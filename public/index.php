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

$myApp = Core\AppFactory::buildMvc( 'mike', false, Core\Responder::TYPE_HTML );

//$myApp->responder()->respond( $myApp->run() );

// TODO:
// Add API Protection (oauth)

// TODO:
// Unit Testing:
// add tests for utils

