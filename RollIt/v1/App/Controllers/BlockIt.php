<?php
namespace Ridmic\RollIt;

require_once CORE_DIR . "Controller.php";

use Ridmic\Core as Core;

class BlockItController extends Core\Controller
{
    public function block()
    {
        return $this->makeResponse( Core\ResponseCode::CODE_FORBIDDEN );
    }

    // Overrides
    protected function registerRoutes()
    {
        $this->addBefore( 'ALL', '{:any}', [$this, 'block' ] );        
    }
}
