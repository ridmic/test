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
    const TURN_MAX          = 13;
    const SCORE_BONUS       = 35;
    const YAHTZEE_BONUS     = 100;
    const SCORE_BONUS_MIN   = 63;
    
    protected $turns        = [];
    protected $rolls        = 0;
    protected $currentTurn  = null;
    protected $bonus        = 0;
    protected $bonusYahtzee = 0;
     
    public function __construct()
    {
        $this->currentTurn = new Turn();
        $this->startGame();
    }
     
    // Access
    public function currentTurn()   { return $this->currentTurn; }
    
    // Play
    public function startGame()
    {
        $this->turns = [];
        $this->rolls = 0;
        $this->bonus = 0;
        return true;
    }
    
    public function startTurn()
    {
        return $this->currentTurn->startTurn();
    }
     
    public function takeTurn( $hold = [] )
    {
        if ( is_object($this->currentTurn) )
        {
            $this->rolls++;
            return $this->currentTurn->takeTurn( $hold );
        }
    }
    
    public function endTurn( $type )
    {
        if ( $this->currentTurn->endTurn( $type ) >= 0 )
        {
            // Check for multiple yahtzee's
            if ( $this->currentTurn->isYahtzee() && array_key_exists(Turn::TURN_TYPE_YZ, $this->turns ) )
                $this->bonusYahtzee += self::YAHTZEE_BONUS;
                
            // Save this turn away against its type
            if ( ! isset($this->turns[$type]) )
            {
                $this->turns[$type] = $this->currentTurn->turn(); 
                return true;
            }
        }
        return false;
    }
    
    public function endGame()
    {
    }
    
    public function scoreGame()
    {
        // Top Score
        $scoreTop = 0;
        for ( $i = Turn::TURN_TYPE_1S ; $i <= Turn::TURN_TYPE_6S ; $i++ )
        {
            $this->currentTurn->loadTurn( $this->turns[$i] );
            $scoreTop += $this->currentTurn->getScore();
        }
        // Bonus?
        $this->bonus = 0;
        if ( $scoreTop >= self::SCORE_BONUS_MIN )
            $this->bonus = self::SCORE_BONUS;

        // Top Score
        $scoreLower = 0;
        for ( $i = Turn::TURN_TYPE_3X ; $i <= Turn::TURN_TYPE_YZ ; $i++ )
        {
            $this->currentTurn->loadTurn( $this->turns[$i] );
            $scoreLower += $this->currentTurn->getScore();
        }
        
        return [ 'top'    => $scoreTop, 
                 'bonus'  => $this->bonus, 
                 'lower'  => $scoreLower, 
                 'ybonus' => $this->bonusYahtzee, 
                 'total'  => $scoreTop + $this->bonus + $scoreLower + $this->bonusYahtzee];
    }
    
    public function scoreGameAsJson()       { return json_encode( $this->scoreGame() ); }
    public function scoreGameAsXml()        { return $this->arrayToXML( $this->scoreGame(), new SimpleXMLElement('<score/>'), 'xxx' ); }
    
    public function arrayToXML($array, SimpleXMLElement $xml, $child_name)
    {
        foreach ($array as $k => $v) {
            if(is_array($v)) {
                (is_int($k)) ? $this->arrayToXML($v, $xml->addChild($child_name), $v) : $this->arrayToXML($v, $xml->addChild(strtolower($k)), $child_name);
            } else {
                (is_int($k)) ? $xml->addChild($child_name, $v) : $xml->addChild(strtolower($k), $v);
            }
        }
    
        return $xml->asXML();
    }
    

    public function bestTurnBasedScore()
    {
        // Find the top score
        $scores = [];
        for ( $type = Turn::TURN_TYPE_1S ; $type <= Turn::TURN_TYPE_YZ ; $type++ )
            $scores[ $this->currentTurn->turnTypeString($type) ] = $this->currentTurn->getTurnBasedScore($type, true );
        asort( $scores, SORT_NUMERIC );
        $scores = array_reverse( $scores );
        // Scan for the best
        foreach ( $scores as $strType => $score )
        {
            // Do we have this already (ignore chance)?
            $type = $this->currentTurn->turnTypeFromString($strType);
            if ( ! isset($this->turns[$type]) && $strType != 'CH' )
            {
                return $type;   
            }
        }
        return Turn::TURN_TYPE_CH;
    }

    public function autoHold( $type )
    {
        // Initialise
        $score  = -1;
        $die    = $this->currentTurn->getDie();
        $hold   = [ false, false, false, false, false ];

        // Convert the INTs into strings
        foreach ( $die as $dice => $value  )
            $dieArray[] = "$value";
        sort( $dieArray );
        $diceString = implode( '', $dieArray );    
        $arrCounts  = count_chars( $diceString, 1 );
        // Sort the counts in descending order
        arsort( $arrCounts );

        // Validate
        if ( $type >= Turn::TURN_TYPE_1S && $type <= Turn::TURN_TYPE_YZ )
        {
            switch ( $type )
            {
                // For these we hold the required number regardless of count
                case Turn::TURN_TYPE_1S:
                case Turn::TURN_TYPE_2S:
                case Turn::TURN_TYPE_3S:
                case Turn::TURN_TYPE_4S:
                case Turn::TURN_TYPE_5S:
                case Turn::TURN_TYPE_6S:
                    for ( $i = Turn::DICE_1 ; $i <= Turn::DICE_5 ; $i++ )
                        $hold[ $i ] = $die[ $i ] == $type ? true : false;
                    break;
                    
                // For these we just hold the largest value
                case Turn::TURN_TYPE_3X:
                case Turn::TURN_TYPE_4X:
                case Turn::TURN_TYPE_YZ:
                    $keys   = array_keys( $arrCounts );
                    $toHold = $keys[0] - 48;
                    for ( $i = Turn::DICE_1 ; $i <= Turn::DICE_5 ; $i++ )
                        $hold[ $i ] = $die[ $i ] == $toHold ? true : false;
                    break;
                    
                // For these we just hold the largest 2 values
                case Turn::TURN_TYPE_FH:
                    $keys   = array_keys( $arrCounts );
                    $toHold1 = $keys[0] - 48;
                    $toHold2 = $keys[1] - 48;
                    for ( $i = Turn::DICE_1 ; $i <= Turn::DICE_5 ; $i++ )
                        $hold[ $i ] = $die[ $i ] == $toHold1 || $die[ $i ] == $toHold2 ? true : false;//var_dump($hold);
                    break;
                    
                // For this we want to hold numbers in sequence, but ony of of each number
                case Turn::TURN_TYPE_SS:
                case Turn::TURN_TYPE_LS:
                    // Look for sequencial numbers
                    $toHold = array();
                    $chars = str_split( $diceString, 1 );
                    for ($i = 0 ; $i < count($chars)-1 ; $i++ )
                    {
                        if ( $chars[$i+1] - $chars[$i] == 1 )
                        {
                            $toHold[] = $chars[$i];
                            $toHold[] = $chars[$i+1];
                        }
                    }
                    if ( count($toHold) )
                    {
                        // Unique values only
                        $toHold = array_unique( $toHold );
                        $held   = array();
                        for ( $i = Turn::DICE_1 ; $i <= Turn::DICE_5 ; $i++ )
                        {
                            if ( in_array($die[ $i ], $toHold ) && !in_array( $die[$i], $held ))
                            {
                                $hold[ $i ] = true;
                                $held[]     = $die[ $i ];
                            }
                        }
                    }
                    break;

                // For this, just select anything > 4
                case Turn::TURN_TYPE_CH:
                     for ( $i = Turn::DICE_1 ; $i <= Turn::DICE_5 ; $i++ )
                        $hold[ $i ] = $die[ $i ] > 4 ? true : false;
                   break;
                    
                default:
                    break;
            }
        }
        return $hold;
    }

    public function getTurnAsString()
    {
        return "TURN : {".$this->currentTurn->getTurn()."}{".$this->currentTurn->getTurnType()."} [".implode( ', ', $this->currentTurn->getDie())."] = ".$this->currentTurn->getScore();
    }
    
    public function getScoreAsString()
    {
        return "SCORE : {".$this->currentTurn->turnTypeString($this->currentTurn->getTurnType())."} = [".$this->currentTurn->getScore()."]";
    }
     
}

class Turn
{
    const TURN_MASK         = '11111|1111|11|111111|111|111|111|111|111';   
    
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
    
    const DICE_1            = 0;
    const DICE_2            = 1;
    const DICE_3            = 2;
    const DICE_4            = 3;
    const DICE_5            = 4;

    const SCORE             = 5;
    const TURN              = 6;
    const TURN_TYPE         = 7;

    const THROW_MAX         = 3;
    const DICE_VALUE_MIN    = 1;
    const DICE_VALUE_MAX    = 6;
    
    protected $strTypes     = [ '??', '1S','2S','3S','4S','5S','6S','3X','4X','FH','SS','LS','CH','YZ'];
    protected $strScores    = [  0,    1,   2,   3,   4,   5,   6,   3,   4,   25,  30,  40,   0,  50 ];
    protected $slots        = null;
    
    public function __construct()
    {
        $this->slots = new Utils\BitSlots();  
        $this->slots->setMask( self::TURN_MASK );
    }
    
    // Access
    public function turn()                          { return $this->slots->get(); }
    public function loadTurn( $turnID )             { $this->slots->set( $turnID ); }
    public function turnTypeString( $type )         { return $type >= self::TURN_TYPE_1S && $type <= self::TURN_TYPE_YZ ? $this->strTypes[ $type ] : $this->strTypes[ 0 ]; }
    public function turnTypeScore( $type )          { return $type >= self::TURN_TYPE_1S && $type <= self::TURN_TYPE_YZ ? $this->strScores[ $type ] : $this->strScores[ 0 ]; }
    public function turnTypeFromString( $sType )    { return array_search( strtoupper($sType), $this->strTypes ); }

    // Turns
    public function startTurn()                     
    {  
        return ( $this->slots->clear() == 0 ? true : false ); 
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
        return $score;
    }

    // Slot Access - GET
    public function getTurnType()           { return $this->slots->getSlot( self::TURN_TYPE ); }
    public function getDice( $index )       { return $index >= self::DICE_1 && $index <= self::DICE_5 ?  $this->slots->getSlot( $index ) : 0; }
    public function getScore()              { return $this->slots->getSlot( self::SCORE ); }
    public function getTurn()               { return $this->slots->getSlot( self::TURN ); }
    public function getDie()
    {
        $die = array();
        for ( $i = self::DICE_1 ; $i <= self::DICE_5 ; $i++ )
            $die[$i] = $this->getDice($i);
        return $die;
    }

    // Slot Access - SET
    public function setTurnType( $type )
    {
        // Initialise
        $setDice = false;
        $type    = intval($type);

        // Validate
        if ( $type >= self::TURN_TYPE_1S && $type <= self::TURN_TYPE_YZ && $this->getTurnType() == self::TURN_TYPE_NONE )
        {
            // Set the appropriate slot
            $setDice = $this->slots->setSlot( self::TURN_TYPE, $type );
        }
        return $setDice;
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
                $setDice = $this->slots->setSlot( $index, $value );
            }
        }
        return $setDice;
    }
 
    public function setScore( $value = -1 )
    {
        // Initialise
        $value = $value == -1 ? $this->score() : intval($value);
        return $this->slots->setSlot( self::SCORE, $value );
    }

    public function setTurn( $value = -1 )
    {
        // Initialise
        $value = $value == -1 ? $this->getTurn()+1 : intval($value);
        if ( $value <= self::THROW_MAX )
            return $this->slots->setSlot( self::TURN, $value );
        return false;
    }
   
   // --

    public function rollDice( $index )
    {
        return $this->setDice( $index, rand(1, 6) );
    }
    

    public function score()
    {
        $score = 0;
        for ( $i = self::DICE_1 ; $i <= self::DICE_5 ; $i++ )
            $score += $this->slots->getSlot($i);
        return $score;
    }

    public function isYahtzee()
    {
        // Convert the INTs into strings
        foreach ( $this->getDie() as $dice => $value  )
            $dieArray[] = "$value";
        return count(array_unique( $dieArray )) == 1;
    }
    
    public function getTurnBasedScore( $type, $weighted = false )
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
                    $score  = $this->turnTypeScore( self::TURN_TYPE_1S ) * substr_count($diceString, '1');
                    $score += $weighted ? 10 * substr_count($diceString, '1') : 0;
                    break;
                    
                case self::TURN_TYPE_2S:
                    // Count the number of 2's and sum them.
                    $score  = $this->turnTypeScore( self::TURN_TYPE_2S ) * substr_count($diceString, '2');
                    $score += $weighted ? 10 * substr_count($diceString, '2') : 0;
                    break;
                    
                case self::TURN_TYPE_3S :
                    // Count the number of 3's  and sum them.
                    $score  = $this->turnTypeScore( self::TURN_TYPE_3S ) * substr_count($diceString, '3');
                    $score += $weighted ? 10 * substr_count($diceString, '3') : 0;
                    break;
                    
                case self::TURN_TYPE_4S:
                    // Count the number of 4's and sum them.
                    $score  = $this->turnTypeScore( self::TURN_TYPE_4S ) * substr_count($diceString, '4');
                    $score += $weighted ? 10 * substr_count($diceString, '4') : 0;
                    break;
                    
                case self::TURN_TYPE_5S:
                    // Count the number of 5's and sum them.
                    $score  = $this->turnTypeScore( self::TURN_TYPE_5S ) * substr_count($diceString, '5');
                    $score += $weighted ? 10 * substr_count($diceString, '5') : 0;
                    break;
                    
                case self::TURN_TYPE_6S:
                    // Count the number of 1's and sum them.
                    $score  = $this->turnTypeScore( self::TURN_TYPE_6S ) * substr_count($diceString, '6');
                    $score += $weighted ? 10 * substr_count($diceString, '6') : 0;
                    break;
                    
                case self::TURN_TYPE_3X:
                    // 3 of any number
                    $score = 0;
                    foreach ( $arrCounts as $i => $val) 
                    {
                        if ( $val >= 3 )
                        {
                            $score = $weighted ? $this->score() + 100 : $this->score();
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
                            $score = $weighted ? $this->score() + 200 : $this->score();
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
                        $score = $weighted ? $this->turnTypeScore( self::TURN_TYPE_FH ) + 300 : $this->turnTypeScore( self::TURN_TYPE_FH );
                        break;
                    }
                    break;
                    
                case self::TURN_TYPE_SS:
                    // 4 in a row
                    $score = 0;
                    $diceString = array_unique( str_split( $diceString ) );
                    sort($diceString);
                    $diceString = implode( '', $diceString );
                    $matches    = ['1234','12345','12346','2345','23456' ];
                    if ( in_array($diceString, $matches ))
                        $score = $weighted ? $this->turnTypeScore( self::TURN_TYPE_SS ) + 300 : $this->turnTypeScore( self::TURN_TYPE_SS );
                    break;
                    
                case self::TURN_TYPE_LS:
                    // 5 in a row
                    $score = 0;
                    if ( $diceString == '12345' || $diceString == '23456' )
                        $score = $weighted ? $this->turnTypeScore( self::TURN_TYPE_LS ) + 300 : $this->turnTypeScore( self::TURN_TYPE_LS );
                    break;
                    
                case self::TURN_TYPE_CH:
                    // any score
                    $score = $this->score();
                    break;
                    
                case self::TURN_TYPE_YZ:
                    // 5 of any number !!!! NOT WORKING!!!!!
                    $score = 0;
                    foreach ( $arrCounts as $i => $val) 
                    {
                        if ( $val == 5 )
                        {
                            $score = $weighted ? $this->turnTypeScore( self::TURN_TYPE_YZ ) + 300 : $this->turnTypeScore( self::TURN_TYPE_YZ );
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

    // Magic Functions
    public function __toString()
    {
        return $this->slots->showSlots();
    }      
}

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


/*
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
    for ( $i = 0 ; $i < Turn::THROW_MAX ; $i++ )
    {
        $turn->takeTurn($hold);
        Core\Debug::write( "TURN : {".$turn->getTurn()."}{".$turn->getTurnType()."} [".implode( ', ', $turn->getDie())."] = ".$turn->getScore());
        
    }
    $turn->endTurn( $play );
    Core\Debug::write( "SCORE : {".$turn->turnTypeString($turn->getTurnType())."} = [".$turn->getScore()."]");
}
*/