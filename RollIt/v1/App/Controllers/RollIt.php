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

    public function double( $dice = 1 )
    {
        $bits =  $this->app->dispatcher()->decomposeRoute( $this->app->isVersioned() );
        $bits['rest'] = [ min( 10, max( 1, $dice * 2)) ];
        $this->app->dispatcher()->composeRoute( $bits, $this->app->isVersioned() );

        return $this->makeResponse( Core\ResponseCode::CODE_OK );
    }


    // Overrides
    protected function registerRoutes()
    {
//        $this->addBefore( 'ALL', '{:any}', [$this, 'reroute' ] );        
        $this->addBefore( 'GET', 'roll/{:id}', [$this, 'double' ] );        
        
        
        $this->addRoute( 'GET', 'roll/{:id}', [$this, 'roll' ] );        
    }
}
