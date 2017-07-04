<?php
namespace Ridmic\Mike;

require_once __DIR__ . "/../../../Core/Controller.php";
require_once __DIR__ . "/../../../Core/View.php";

use Ridmic\Core as Core;

class TestController extends Core\Controller
{
    public function user( $id=0 )
    {
        $this->view->assign( 'param1', $id );
        return $this->view->render( 'test' );
    } 

    public function reroute()
    {
        $this->app->dispatcher()->reroute( 'user' );
        return true;
    }

    // Overrides
    protected function registerRoutes()
    {
        $this->addBefore( 'ALL', '{:any}', [$this, 'reroute' ] );        
        
        $this->addRoute( 'GET', 'user/{:id}', [ $this, 'user' ] );        
    }
}

