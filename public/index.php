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
     // Bonus + Total
    const SCORE_BONUS       = 35;
    const YAHTZEE_BONUS     = 100;
    
    const SCORE_BONUS_OVER  = 62;
     
     function xx()
     {
             // Special rules for subsequent Yahtzee's so we need to wrk it out up front
        $yahtzee = false;
        foreach ( $arrCounts as $i => $val) 
            if ( $val == 5 )
                $yahtzee = true;

     }
}

class Turn
{
    const TURN_MASK         = '111111|1111|11|11111|111|111|111|111|111';   
    
    const TURN_TYPE_NONE    = 0;
    const TURN_TYPE_1S      = 1;
    const TURN_TYPE_2S      = 2;
    const TURN_TYPE_3S      = 3;
    const TURN_TYPE_4S      = 4;
    const TURN_TYPE_5S      = 5;
    const TURN_TYPE_6S      = 6;
    const TURN_TYPE_3X      = 7;
    const TURN_TYPE_4X      = 8;
    const TURN_TYPE_FH      = 9;
    const TURN_TYPE_SS      = 10;
    const TURN_TYPE_LS      = 11;
    const TURN_TYPE_CH      = 12;
    const TURN_TYPE_YZ      = 13;
    
    const SCORE_1S          = 1;
    const SCORE_2S          = 2;
    const SCORE_3S          = 3;
    const SCORE_4S          = 4;
    const SCORE_5S          = 5;
    const SCORE_6S          = 6;
    const SCORE_3X          = 3;
    const SCORE_4X          = 4;
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

    const SCORE             = 5;
    const TURN              = 6;
    const TURN_TYPE         = 7;

    const TURN_MAX          = 3;
    const DICE_VALUE_MIN    = 1;
    const DICE_VALUE_MAX    = 6;
    
    protected $slots        = null;
    
    public function __construct()
    {
        $this->slots = new Utils\BitSlots();  
        $this->slots->setMask( self::TURN_MASK );
    }
    
    public function startTurn()
    {
        $this->slots->clear();    
    }
    
    public function takeTurn( $hold = [] )
    {
        $hold = array_replace( array(false, false, false, false, false ), $hold );
        
        // Have we already saved this as a turn?
        if ( $this->getTurnType() == self::TURN_TYPE_NONE )
        {
            if ( $this->setTurn() )
            {
                // Roll all the dice
                for ( $i = self::DICE_1 ; $i <= self::DICE_5 ; $i++ )
                {
                    if ( array_key_exists( $i, $hold ) && $hold[$i] == false )
                        $this->rollDice( $i );
                }
                // And set the score
                $this->setScore();
                return true;
            }
        }
        return false;
    }
    
    public function endTurn( $type )
    {
        $score = $this->getTurnBasedScore( $type );
        if ( $score >= 0 )
        {
            // Set the turn type and score
            if ( $this->setTurnType( $type ) )
            {
                $this->setScore( $score );
            }
        }
        $score;
    }

    public function setTurnType( $type )
    {
        // Initialise
        $setType = false;
        $type    = intval($type);

        // Validate
        if ( $type >= self::TURN_TYPE_1S && $type <= self::TURN_TYPE_YZ )
        {
            if ( $this->getTurnType() == self::TURN_TYPE_NONE )
            {
                // Set the appropriate slot
                $this->slots->setSlot( self::TURN_TYPE, $type );
                $setType = true;
            }
        }
        return $setType;
    }
    
    public function getTurnType()
    {
        // Initialise
        return $this->slots->getSlot( self::TURN_TYPE );
    }
    
    public function bestTurnBasedScore()
    {
        // TODO: Not working 
        
        $scores = [];
        for ( $type = self::TURN_TYPE_1S ; $type <= self::TURN_TYPE_YZ ; $type++ )
            $scores[ $type ] = $this->getTurnBasedScore($type);
        var_dump($scores);
        sort( $scores, SORT_NUMERIC );
        $scores = array_reverse( $scores );
        var_dump($scores);
        
        return array_keys($scores)[0];
    }

    public function getTurnBasedScore( $type )
    {
        // Initialise
        $score  = -1;
        $die    = $this->getDie();

        // Convert the INTs into strings
        foreach ( $die as $dice => $value  )
            $dieArray[] = "$value";
        $diceString = implode( '', $dieArray );    
        $arrCounts  = count_chars( $diceString, 1 );

        // Sort it
        $arr = str_split($diceString, 1);
        sort($arr);
        $diceString = implode('', $arr);
        
        // Validate
        if ( $type >= self::TURN_TYPE_1S && $type <= self::TURN_TYPE_YZ )
        {
            switch ( $type )
            {
                case self::TURN_TYPE_1S:
                    // Count the number of 1's and sum them.
                    $score = self::SCORE_1S * substr_count($diceString, '1');
                    break;
                    
                case self::TURN_TYPE_2S:
                    // Count the number of 2's and sum them.
                    $score = self::SCORE_2S * substr_count($diceString, '2');
                    break;
                    
                case self::TURN_TYPE_3S :
                    // Count the number of 3's  and sum them.
                    $score = self::SCORE_3S * substr_count($diceString, '3');
                    break;
                    
                case self::TURN_TYPE_4S:
                    // Count the number of 4's and sum them.
                    $score = self::SCORE_4S * substr_count($diceString, '4');
                    break;
                    
                case self::TURN_TYPE_5S:
                    // Count the number of 5's and sum them.
                    $score = self::SCORE_5S * substr_count($diceString, '5');
                    break;
                    
                case self::TURN_TYPE_6S:
                    // Count the number of 1's and sum them.
                    $score = self::SCORE_6S * substr_count($diceString, '6');
                    break;
                    
                case self::TURN_TYPE_3X:
                    // 3 of any number
                    $score = 0;
                    foreach ( $arrCounts as $i => $val) 
                    {
                        if ( $val >= 3 )
                        {
                            $score = $this->score();
                            break;
                        }
                    }
                    break;
                    
                case self::TURN_TYPE_4X:
                    // 4 of any number
                    $score = 0;
                    foreach ( $arrCounts as $i => $val) 
                    {
                        if ( $val >= 4 )
                        {
                            $score = $this->score();
                            break;
                        }
                    }
                    break;
                    
                case self::TURN_TYPE_FH:
                    // 2 of one and 3 of another
                    $tmp   = array();
                    $score = 0;
                    foreach ( $arrCounts as $i => $val) 
                    {
                        if ( $val == 3 )
                          $tmp['3'] = true;
                        if ( $val == 2 )
                          $tmp['2'] = true;
                    }
                    if ( count($tmp) == 2 )
                    {
                        $score = self::SCORE_FH;
                        break;
                    }
                    break;
                    
                case self::TURN_TYPE_SS:
                    // 4 in a row
                    $score = 0;
                    $diceString = implode( '', array_unique( str_split( $diceString ) ));
                    if ( strlen($diceString) >= 4 && strpos($diceString, '4') !== false )
                        $score = self::SCORE_SS;
                    break;
                    
                case self::TURN_TYPE_LS:
                    // 5 in a row
                    $score = 0;
                    if ( $diceString == '12345' || $diceString == '23456' )
                        $score = self::SCORE_LS;
                    break;
                    
                case self::TURN_TYPE_CH:
                    // any score
                    $score = $this->score();
                    break;
                    
                case self::TURN_TYPE_YZ:
                    // % of any number
                    $score = 0;
                    foreach ( $arrCounts as $i => $val) 
                    {
                        if ( $val == 5 )
                        {
                            $score = self::SCORE_YZ;
                            break;
                        }
                    }
                    break;
                    
                default:
                    break;
            }
        }
        return $score;
    }
   
    public function turnTypeAsString( $type )
    {
        $strType  = '';
        $strArray = ['1S','2S','3S','4S','5S','6S','3X','4X','FH','SS','LS','CH','YZ'];
        
        // Validate
        if ( $type >= self::TURN_TYPE_1S && $type <= self::TURN_TYPE_YZ )
            $strType = $strArray[ $type-1 ];
        return $strType;
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
        if ( $index >= DICE_1 && $index <= self::DICE_5 )
        {
            if ( $value >= self::DICE_VALUE_MIN && $value <= self::DICE_VALUE_MAX )
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
        if ( $index >= self::DICE_1 && $index <= self::DICE_5 )
        {
            // Get the appropriate bits
            $value = $this->slots->getSlot( $index );
        }
        return $value;
    }
    public function getDie()
    {
        $die = array();
        for ( $i = self::DICE_1 ; $i <= self::DICE_5 ; $i++ )
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
        for ( $i = self::DICE_1 ; $i <= self::DICE_5 ; $i++ )
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

$plays = [  Turn::TURN_TYPE_1S => 1,
            Turn::TURN_TYPE_2S => 2,
            Turn::TURN_TYPE_3S => 3,
            Turn::TURN_TYPE_4S => 4,
            Turn::TURN_TYPE_5S => 5,
            Turn::TURN_TYPE_6S => 6
         ];

foreach ( $plays as $play => $val )
{
    $turn->startTurn();
    for ( $i = 0 ; $i < Turn::TURN_MAX ; $i++ )
    {
        $turn->takeTurn($hold);
        Core\Debug::write( "TURN : {".$turn->getTurn()."}{".$turn->getTurnType()."} [".implode( ', ', $turn->getDie())."] = ".$turn->getScore());
        
        $type = $turn->bestTurnBasedScore();        
        
        // Try and get 3's
        foreach ( $turn->getDie() as $dice => $value )
        {
            $hold[$dice] = $value == $val ? true : false;
            $holdT[$dice] = $value == $val ? 'T' : 'F';
        }
        Core\Debug::write( "HOLD : {".$turn->getTurn()."}{".$turn->getTurnType()."} [".implode( ', ', $holdT)."]");
    }
    $turn->endTurn( $play );
    Core\Debug::write( "SCORE : {".$turn->turnTypeAsString($turn->getTurnType())."} = [".$turn->getScore()."]");
}
