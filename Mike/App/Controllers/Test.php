<?php
namespace Ridmic\Mike;

require_once __DIR__ . "/../../../Core/Controller.php";

use Ridmic\Core as Core;

class TestController extends Core\Controller
{
    public function user( $id=0)
    {
        Core\Debug::debug( "---> Called: TestController@test($id)!" );
        
        return $this->makeResponse( Core\ResponseCode::CODE_OK, [ 'name1' => 'value1', 'name2' => [ 'value11', 'value12'] , 'name3' => 'value3' ] );
    } 

    // Overrides
    protected function registerRoutes( Core\Router $router )
    {
        $router->route()->add( 'GET', '/test/user/{:id}', [$this, 'user' ] );        
    }
}

