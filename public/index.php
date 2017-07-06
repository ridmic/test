<?php
// Pull in our framework config
require __DIR__ . "/../Core/Config.php";

use Ridmic\Core as Core;

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

//$myApp = Core\AppFactory::buildMvc( 'roll_it', true );

//$controller = $myApp->loadAltController( 'block_it' );
//$controller = $myApp->loadCoreController( 'auth_api_key' );
//if ( !is_null($controller) )
//{
//    $controller->setApiKey( 'password' );
//    $myApp->responder()->respond( $myApp->run() );
//}

//$responder  = new Core\Responder( Core\Responder::TYPE_HTMLPAGE );
//$myApp      = Core\AppFactory::buildMvc( 'mike' );
//$myApp->responder()->respond( $myApp->run() );

// TODO:
// Add API Protection (token, jwt, oauth)
// Add Form Protecion
// Add Other protection

require_once CORE_DIR . "Utils/JwtClaim.php";

$secret = 'aaa';
$jwt    = new Core\Utils\JwtClaim();
$jwt->setIssuer('issuer');
$jwt->setSubject('subject');
$jwt->setAudience('audience');
$jwt->setNotBefore(time()+10);
$jwt->setExpiration(time()+60);
$jwt->setIssued(time());
$jwt->setIdentifier(Core\Utils\Secure::generateToken());

$jwt->addContextClaim( 'param1', 'value1' );
$jwt->addContextClaim( 'param2', 'value2' );
$jwt->addContextClaim( 'param3', 'value3' );


$token  = $jwt->encode( $secret );

var_dump( $token );
var_dump(Core\Utils\Jwt::asArray( $token, $secret ) );


Core\Debug::debug('END');
