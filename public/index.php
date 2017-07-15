<?php

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

//$myApp = Core\AppFactory::buildMvc( 'mike', false, Core\Responder::TYPE_HTML );

//$myApp->responder()->respond( $myApp->run() );

// TODO:
// Add API Protection (oauth)

// TODO:
// Unit Testing:
// add tests for utils

// TODO: Finish header and unittest

include CORE_DIR . "Utils/Bits.php";

class Game
{
    
}

class Turn
{
    const TURN_MASK         = '11|1111|11|111111|111|111|111|111|111|111';   
    
    const COMBINATION_1S    = 1;
    const COMBINATION_2S    = 2;
    const COMBINATION_3S    = 3;
    const COMBINATION_4S    = 4;
    const COMBINATION_5S    = 5;
    const COMBINATION_6S    = 6;
    const COMBINATION_3X    = 7;
    const COMBINATION_4X    = 8;
    const COMBINATION_FH    = 9;
    const COMBINATION_SS    = 10;
    const COMBINATION_LS    = 11;
    const COMBINATION_CH    = 12;
    const COMBINATION_YZ    = 13;
    
    const SCORE_1S          = 0;
    const SCORE_2S          = 0;
    const SCORE_3S          = 0;
    const SCORE_4S          = 0;
    const SCORE_5S          = 0;
    const SCORE_6S          = 0;
    const SCORE_3X          = 15;
    const SCORE_4X          = 20;
    const SCORE_FH          = 25;
    const SCORE_SS          = 30;
    const SCORE_LS          = 40;
    const SCORE_CH          = 0;
    const SCORE_YZ          = 50;

    const DICE_1            = 0;
    const DICE_2            = 1;
    const DICE_3            = 2;
    const DICE_4            = 3;
    const DICE_5            = 4;
    const DICE_6            = 5;

    const SCORE             = 6;
    const TURN              = 7;

    const TURN_MAX          = 3;
    const DICE_VALUE_MIN    = 1;
    const DICE_VALUE_MAX    = 6;
    
    protected $slots        = null;
    
    public function __construct()
    {
        $this->slots = new Utils\BitSlots();  
        $this->slots->setMask( self::TURN_MASK );
    }
    
    public function takeTurn( $hold = [] )
    {
        $hold = array_replace( array(false, false, false, false, false, false ), $hold );
        if ( $this->setTurn() )
        {
            // Roll all the dice
            for ( $i = self::DICE_1 ; $i <= self::DICE_6 ; $i++ )
            {
                if ( array_key_exists( $i, $hold ) && $hold[$i] == false )
                    $this->rollDice( $i );
            }
            // And set the score
            $this->setScore();
            return true;
        }
        return false;
    }
    
    public function rollDice( $index )
    {
        return $this->setDice( $index, rand(1, 6) );
    }
    
    public function setDice( $index, $value )
    {
        // Initialise
        $setDice = false;
        $index   = intval($index);
        $value   = intval($value);
        
        // Validate
        if ( $index >= DICE_1 && $index <= self::DICE_6 )
        {
            if ( $value >= DICE_VALUE_MIN && $value <= self::DICE_VALUE_MAX )
            {
                // Set the appropriate slot
                $this->slots->setSlot( $index, $value );
                $setDice = true;
            }
        }
        return $setDice;
    }
    
    public function getDice( $index )
    {
        // Initialise
        $index   = intval($index);
        $value   = 0;
        
        // Validate
        if ( $index >= DICE_1 && $index <= self::DICE_6 )
        {
            // Get the appropriate bits
            $value = $this->slots->getSlot( $index );
        }
        return $value;
    }
    public function getDie()
    {
        $die = array();
        for ( $i = self::DICE_1 ; $i <= self::DICE_6 ; $i++ )
            $die[$i] = $this->getDice($i);
        return $die;
    }

    public function setScore( $value = -1 )
    {
        // Initialise
        $value = $value == -1 ? $this->score() : intval($value);
        return $this->slots->setSlot( self::SCORE, $value );
    }

    public function getScore()
    {
        // Initialise
        return $this->slots->getSlot( self::SCORE );
    }

    public function setTurn( $value = -1 )
    {
        // Initialise
        $value = $value == -1 ? $this->getTurn()+1 : intval($value);
        if ( $value <= self::TURN_MAX )
            return $this->slots->setSlot( self::TURN, $value );
        return false;
    }

    public function getTurn()
    {
        // Initialise
        return $this->slots->getSlot( self::TURN );
    }

    protected function score()
    {
        $score = 0;
        for ( $i = self::DICE_1 ; $i <= self::DICE_6 ; $i++ )
            $score += $this->slots->getSlot($i);
        return $score;
    }

    // Magic Functions
    public function __toString()
    {
        return $this->slots->showSlots();
    }      
}

$turn  = new Turn();
$hold  = array();
$holdT = array();
for ( $i = 0 ; $i < Turn::TURN_MAX ; $i++ )
{
    $turn->takeTurn($hold);
    Core\Debug::write( "TURN : {".$turn->getTurn()."} [".implode( ', ', $turn->getDie())."] = ".$turn->getScore());
    
    // Try and get 3's
    foreach ( $turn->getDie() as $dice => $value )
    {
        $hold[$dice] = $value == 6 ? true : false;
        $holdT[$dice] = $value == 6 ? 'T' : 'F';
    }
    Core\Debug::write( "HOLD : {".$turn->getTurn()."} [".implode( ', ', $holdT)."]");
}


