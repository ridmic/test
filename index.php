<?php
include "header.php";
include "core/object.php";
include "core/router.php";
include "core/dispatcher.php";

use Ridmic\Core as Core;

Core\Object::defDebugLevel( Core\Object::DBG_TRACE );
Core\Object::defShowDateTime( false );

function test( $id = 0)
{
  echo "Called: test($id)!";
}

class myTest
{
  public function test( $id=0)
  {
     echo "Called: myTest@test($id)!";
  }
  public function testUser( $id=0, $v=null )
  {
     echo "Called: myTest@testUser($id, $v)!";
  }
}
  
  
$router = new Core\Router();

// Set some routes
$router->get( 'user1/{:id}', 'test' );
$router->get( 'user2/{:id}', 'myTest@test' );
$router->get( 'user2/{:id}/does/{:id}', 'myTest@testUser' );

$response = $router->match( 'get', 'user1/111' );
$response->dispatch();

$response = $router->match( 'get', 'user2/222' );
$response->dispatch();

$response = $router->match( 'get', 'user2/222/does/44' );
$response->dispatch();


include "footer.php";
?>
