<?php
//include "header.php";

// Pull in our framework config
require __DIR__ . "/../Core/Config.php";

require_once CORE_DIR . "App.php";
require_once CORE_DIR . "Responder.php";
require_once CORE_DIR . "View.php";

use Ridmic\Core as Core;

Core\Debug::level( Core\Debug::DBG_DEBUG );
Core\Debug::showDateTime( false );

Core\Debug::debug('START');
 

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

$responder = new Core\Responder( Core\Responder::TYPE_HTML );
$myApp     = Core\AppFactory::buildMvc( 'roll_it', true );

//$responder  = new Core\Responder( Core\Responder::TYPE_HTMLPAGE );
//$myApp      = Core\AppFactory::buildMvc( 'mike' );

$responder->respond( $myApp->run() );

// Need to build/test re-routing, arg massaging, param massaging, global handler insertion  

// 1) method rerouting:
// DONE

// 2) massaging:
// DONE

// 3) insertion:
// we should be able to insert global function into the route list

// 4) Redirecting:
// we should be able to redirect to a different page/controller via a location header

Core\Debug::debug('END');

//include "footer.php";
?>
