<?php
namespace DryMile\Test;

require_once CORE_DIR . "Controller.php";

use DryMile\Core as Core;

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
