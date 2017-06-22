<?php
//include "header.php";

require_once __DIR__ . "/../Core/App.php";
include_once __DIR__ . "/../Core/Responder.php";

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

$responder = new Core\Responder( Core\Responder::TYPE_HTML);
$myApp     = Core\AppFactory::buildMvc( 'roll_it', true );
$responder->respond( $myApp->run() );

Core\Debug::debug('END');

//include "footer.php";
?>
