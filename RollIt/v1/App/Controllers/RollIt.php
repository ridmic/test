<?php
namespace Ridmic\RollIt;

require_once __DIR__ . "/../../../../Core/Controller.php";

use Ridmic\Core as Core;

class RollItController extends Core\Controller
{
    public function roll( $dice = 1 )
    {
        $rolls = [];
        $dice  = min ( $dice, 10 );
        for ( $i=1 ; $i <= $dice ; $i++ )
            $rolls[ "Roll$i"] = rand(1, 6);
            
        return $this->makeResponse( Core\ResponseCode::CODE_OK , $rolls );
    } 

    // Overrides
    protected function registerRoutes()
    {
        $this->addRoute( 'GET', '/roll_it/v1/roll/{:id}', [$this, 'roll' ] );        
    }
}
