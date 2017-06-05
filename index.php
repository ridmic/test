<?php
include "header.php";
include_once "core/debug.php";
include_once "core/object.php";
include_once "core/request.php";
include_once "core/router.php";
include_once "core/dispatcher.php";

include "core/controller.php";

use Ridmic\Core as Core;

Core\Debug::level( Core\Debug::DBG_DEBUG );
Core\Debug::showDateTime( false );

class myController extends Core\Controller
{
    function __construct( Core\Router $router )
    {
      $this->routes = [ 'get' => [ 'user2/{:id}'               => [$this, 'test'],
                                   'user2/{:id}/does/{:id}'    => [$this, 'testUser'] ] ];
      parent::__construct( $router );
      
    }
    
    public function test( $id=0)
    {
      Core\Debug::write( "---> Called: myController@test($id)!" );
    } 
    public function testUser( $id=0, $v=null )
    {
      Core\Debug::write( "---> Called: myController@testUser($id, $v)!" );
    }
}
  
$request    = new Core\Request();
$router     = new Core\Router();
$dispatcher = new Core\Dispatcher();

$myController = new myController( $router );


$testRoutes = [ 'ddd', 'user1/111', 'user2/222', 'user2/333/does/444' ];   
$request->setMethod( 'get' );
foreach ( $testRoutes as $path )  
{
  // Get a request
  $request->setpath( $path );

  // Try and dispatch it
  if ( $dispatcher->setResponse( $router->match( $request->getMethod(), $request->getPath() ) ) )
    $dispatcher->dispatch();
}

include "footer.php";
?>
