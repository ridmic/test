<?php
namespace Ridmic\RollIt;

require_once CORE_DIR . "Controller.php";

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

    public function reroute()
    {
        $this->app->dispatcher()->reroute( 'roll', $this->app->isVersioned() );
        return $this->makeResponse( Core\ResponseCode::CODE_OK );
    }


    // Overrides
    protected function registerRoutes()
    {
        $this->addBefore( 'ALL', '{:any}', [$this, 'reroute' ] );        
        
        
        $this->addRoute( 'GET', 'roll/{:id}', [$this, 'roll' ] );        
    }
}
