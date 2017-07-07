<?php
// Pull in our framework config
require __DIR__ . "/../Core/Config.php";

use Ridmic\Core as Core;

Core\Debug::level( Core\Debug::DBG_ALWAYS);
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

$myApp = Core\AppFactory::buildMvc( 'roll_it', true, Core\Responder::TYPE_HTML );

//$controller = $myApp->loadAltController( 'block_it' );
//$controller = $myApp->loadCoreController( 'auth_api_key' );

$controller = $myApp->loadCoreControllerByName( 'AuthJwtToken' );

$secret     = 'iamasecretkey';

if ( !is_null($controller) )
{
    //$controller->setApiKey( 'password' );
    $controller->setSecret( $secret );
    $controller->setJwtId( '28e17b24220fe49e0fc4583b8aa90d7cf3749dc51ec3f45707dee18c6ba35f07' );
    $controller->addContextClaim( 'param1', 'value1' );
    $controller->addContextClaim( 'param2', 'value2' );
    $controller->addContextClaim( 'param3', 'value3' );
    $myApp->responder()->respond( $myApp->run() );
}

//$myApp      = Core\AppFactory::buildMvc( 'mike' );

//$myApp->responder()->respond( $myApp->run() );

// TODO:
// Add API Protection (oauth)
// Add Form Protecion
// Add Other protection

/*
require_once CORE_DIR . "Utils/JwtClaim.php";

$jwt = $myApp->makeJwt() ;

$jwt->addContextClaim( 'param1', 'value1' );
$jwt->addContextClaim( 'param2', 'value2' );
$jwt->addContextClaim( 'param3', 'value3' );


$token  = $jwt->encode( $secret );

var_dump( $token );
var_dump(Core\Utils\Jwt::asArray( $token, $secret ) );
*/

Core\Debug::debug('END');
