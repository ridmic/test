<?php namespace DryMile\Mike;

// Pull in our framework config
require __DIR__ . "/../Core/Config.php";

use DryMile\Core        as Core;
use DryMile\Core\Utils  as Utils;

Core\Debug::level( Core\Debug::DBG_TRACE );
Core\Debug::setLogger( new Utils\HtmlLogger(true) );

// ========================================================
// We can create simple closure based app
// ========================================================
//$myApp = Core\AppFactory::build( 'test' );
//$myApp->router()->route()->add( 'GET', '/{:any}', function () { echo 'HELLO!'; } );        
//$myApp->run();
// ========================================================

// ========================================================
// We can create simple function based app
// ========================================================
//function HelloWorld() { echo 'HELLO!'; };
//$myApp = Core\AppFactory::build( 'test' );
//$myApp->router()->route()->add( 'GET', '/{:any}', 'HelloWorld' );        
//$myApp->run();
// ========================================================

// ========================================================
// We can create simple class based app
// ========================================================
//class myClass { public function HelloWorld() { echo 'HELLO!'; } };
//$myApp = Core\AppFactory::build( 'test' );
//$myApp->router()->route()->add( 'GET', '/{:any}', 'myClass@HelloWorld' );        
//$myApp->run();
// ========================================================

// ========================================================
// We can create MVC based app ( handles: /test/user/{:id} )
// ========================================================
//$myApp = Core\AppFactory::buildMvc( 'test' );
//$myApp->run();
// ========================================================

//$myApp = Core\AppFactory::buildMvc( 'roll_it', true, Core\Responder::TYPE_HTML );

$myApp = Core\AppFactory::buildMvc( 'mike', false, Core\Responder::TYPE_HTML );

//$myApp->responder()->respond( $myApp->run() );

// TODO:
// Add API Protection (oauth)

// TODO:
// Unit Testing:
// add tests for utils

// TODO: Finish header and unittest

$myApp->loadPlugin( 'Turn' );
$myApp->loadPlugin( 'Game' );

$game  = new Game();
$hold  = array();
echo "<html><header></header><body><pre>";
if ( $game->startGame() )
{
    Core\Debug::write( "STARTING GAME...");

    for ( $j = 1 ; $j <= Game::TURN_MAX ; $j++ )
    {
        if ( $game->startTurn() )
        {
            $turn = $j;
            $hold = array();
            for ( $i = 0 ; $i < Turn::THROW_MAX ; $i++ )
            {
                $game->takeTurn($hold);
                
                // We would hold some dice here
                $turn = $game->bestTurnBasedScore();
                $hold = $game->autoHold( $turn );
            }
            // End the turn
            if ( $game->endTurn( $turn ) )
            {
                Core\Debug::write( "TURN {$j} -> FINAL " . $game->getScoreAsString() . " FOR " . $game->getTurnAsString() );
            }
            else
            {
                Core\Debug::write( "FAILED TO SAVE TURN!" );
            }
        }
    }
    $game->endGame();
    var_dump( $game->scoreGame() );
    var_dump( $game->scoreGameAsJson() );
    var_dump( $game->scoreGameAsXml() );
}
echo "</pre></body></html>";
