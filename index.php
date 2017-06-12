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
    public function before2()
    {
      Core\Debug::write( "---> Called: myController@before2()!" );
      return true;
    }
    public function after()
    {
      Core\Debug::write( "---> Called: myController@after()!" );
      return true;
    }
    public function after2()
    {
      Core\Debug::write( "---> Called: myController@after2()!" );
      return true;
    }
    
    // Overrides
    protected function registerRoutes( Core\Router $router )
    {
      // Befores
      $router->before()->add( 'ALL', '{:any}', [$this, 'before' ] );        
      $router->before()->add( 'ALL', '{:any}', [$this, 'before2' ] );        
      
      // Befores
      //$router->route()->add( 'GET', 'user2/{:id}(?:/{:name})?', [$this, 'test' ] );        
      $router->route()->add( 'GET', 'user2/{:id}#', [$this, 'test' ] );        
      $router->route()->add( 'GET', 'user2/{:id}/does/{:id}', [$this, 'testUser' ] );        

      // Befores
      $router->after()->add( 'ALL', '{:any}', [$this, 'after' ] );        
      $router->after()->add( 'ALL', '{:any}', [$this, 'after2' ] );        
    }
    
}


function callBack1()
{
    Core\Debug::write('NOT FOUND 1!');
    return true;
}
  
function callBack2()
{
    Core\Debug::write('NOT FOUND 2!');
    return true;
}

class TestClass 
{
    
function callBack3()
{
    Core\Debug::write('NOT FOUND 3!');
    return true;
}

}


$request    = new Core\Request();
$router     = new Core\Router();
$dispatcher = new Core\Dispatcher($router);
/*
$routeList     = new Core\RouteList();
$routeList->add('GET', 'user2/{:any}','callBack0' );
$routeList->add('GET', 'user2/{:id}', 'callBack1' );
$routeList->add('GET', 'user2/{:id}/{:id}/{:id}', 'callBack2' );
*/

//$router->before()->add('GET', '/user2/{:id}', 'callBack1');
//$router->before()->add('GET', '/user2/{:id}', 'callBack1');

//$router->route()->add('GET', '/user2/{:id}', 'callBack2');

//$router->after()->add('GET', '/user2/{:id}', 'TestClass@callBack3');
//$router->after()->add('GET', '/user2/{:id}', 'TestClass@callBack3');


$myController = new myController( $router );


//$testRoutes = [ $baseRoute.'/', $baseRoute.'/ddd', $baseRoute.'/user1/111', $baseRoute.'/user2/222', $baseRoute.'/user2/222/XXX', $baseRoute.'/user2/333/does/444' ];   
$testRoutes = [ '/user2/222/XXX'];   
foreach ( $testRoutes as $path )  
{
    $dispatcher->run('get', $path );
}


/*

$baseRoute = '';
$router->setBaseRoute($baseRoute);
$request->setNotFound( 'callBack');
$myController = new myController( $router );


$testRoutes = [ $baseRoute.'/', $baseRoute.'/ddd', $baseRoute.'/user1/111', $baseRoute.'/user2/222', $baseRoute.'/user2/222/XXX', $baseRoute.'/user2/333/does/444' ];   
foreach ( $testRoutes as $path )  
{
  // Get a request
  $request->setpath( $path );
  $request->run( $dispatcher, $router, false );
}
*/

include "footer.php";
?>
