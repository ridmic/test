<?php
namespace Ridmic\Mike;

require_once CORE_DIR . "Controller.php";
require_once CORE_DIR . "View.php";

use Ridmic\Core as Core;

class TestController extends Core\Controller
{
    public function user( $id=0 )
    {
        $this->view->assign( 'param1', $id );

        return $this->view->render( 'test' );
    } 

    // Overrides
    protected function registerRoutes()
    {
        $this->addRoute( 'GET', 'user/{:id}', [ $this, 'user' ] );        
    }
}

