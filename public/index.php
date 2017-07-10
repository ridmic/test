<?php
// Pull in our framework config
require __DIR__ . "/../Core/Config.php";

use DryMile\Core as Core;

Core\Debug::level( Core\Debug::DBG_DEBUG);
Core\Debug::showDateTime( false );

Core\Debug::debug('START [CORE VER]: ' . CORE_VER );
 
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

//$controller = $myApp->loadAltController( 'block_it' );
//$controller = $myApp->loadCoreController( 'auth_api_key' );

//$controller = $myApp->loadCoreControllerByName( 'AuthJwtToken' );

//$secret     = 'iamasecretkey';

//if ( !is_null($controller) )
//{
    //$controller->setApiKey( 'password' );
    //$controller->setSecret( $secret );
    //$controller->setJwtId( '28e17b24220fe49e0fc4583b8aa90d7cf3749dc51ec3f45707dee18c6ba35f07' );
    //$controller->addContextClaim( 'param1', 'value1' );
    //$controller->addContextClaim( 'param2', 'value2' );
    //$controller->addContextClaim( 'param3', 'value3' );
    //$myApp->responder()->respond( $myApp->run() );
//}

$myApp = Core\AppFactory::buildMvc( 'mike', false, Core\Responder::TYPE_HTML );

$myApp->logger()->write( 'Hello World' );

//$myApp->responder()->respond( $myApp->run() );

// TODO:
// Add API Protection (oauth)

// TODO:
// Unit Testing:
// add tests for utils

// TODO:
// Merge Logger class and Debug class so we can log our debugs
//
// Create a screen logger to match the file logger
// set the debug class to use the screen logger by default
// allow it to use the file logger (or both)

Core\Debug::debug('END');
