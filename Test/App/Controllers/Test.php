<?php
namespace Ridmic\Test;

require_once __DIR__ . "/../../../Core/Controller.php";

use Ridmic\Core as Core;

class TestController extends Core\Controller
{
    public function user( $id=0 )
    {
        Core\Debug::write( "---> Called: myController@test($id)!" );
        return true;
    } 

    // Overrides
    protected function registerRoutes( Core\Router $router )
    {
        $router->route()->add( 'GET', '/test/user/{:id}', [$this, 'user' ] );        
    }
}
