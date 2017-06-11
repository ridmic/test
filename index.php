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

    public function test( $id=0)
    {
      Core\Debug::write( "---> Called: myController@test($id)!" );
      return true;
    } 
    public function testUser( $id=0, $v=null )
    {
      Core\Debug::write( "---> Called: myController@testUser($id, $v)!" );
      return true;
    }
    public function before()
    {
      Core\Debug::write( "---> Called: myController@before()!" );
      return true;
    }
    public function after()
    {
      Core\Debug::write( "---> Called: myController@after()!" );
      return true;
    }
    
    // Overrides
    protected function registerRoutes( Core\Router $router )
    {
      // Befores
      $router->addBefore( 'ANY', '{:any}', [$this, 'before' ] );        
      
      // Befores
      $router->addRoute( 'GET', 'user2/{:id}', [$this, 'test' ] );        
      $router->addRoute( 'GET', 'user2/{:id}/does/{:id}', [$this, 'testUser' ] );        

      // Befores
      $router->addAfter( 'ANY', '{:any}', [$this, 'after' ] );        
    }
    
}

function callBack()
{
    Core\Debug::write('NOT FOUND!');
}
  
$request    = new Core\Request();
$router     = new Core\Router();
$dispatcher = new Core\Dispatcher();

$baseRoute = '';
$router->setBaseRoute($baseRoute);
$request->setNotFound( 'callBack');
$myController = new myController( $router );


$testRoutes = [ $baseRoute.'/', $baseRoute.'/ddd', $baseRoute.'/user1/111', $baseRoute.'/user2/222', $baseRoute.'/user2/333/does/444' ];   
foreach ( $testRoutes as $path )  
{
  // Get a request
  $request->setpath( $path );
  $request->run( $dispatcher, $router );
}

include "footer.php";
?>
