<?php namespace DryMile\Mike;

use DryMile\Core\Utils  as Utils;

include CORE_DIR . "Utils/Bits.php";

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
        if ( $index >= self::DICE_1 && $index <= self::DICE_5 )
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
        // Sort the counts in descending order
        arsort( $arrCounts );

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
                    $keys = array_keys( $arrCounts );
                    $vals = array_values( $arrCounts );
                    $val = $vals[0];
                    $die = $keys[0] - 48;
                    if ( $val >= 3 )
                    {
                        $score = $die * $val;
                        $score = $weighted ? $score + 100 : $score;
                    }
                    break;
                    
                case self::TURN_TYPE_4X:
                    // 4 of any number
                    $score = 0;
                    $keys = array_keys( $arrCounts );
                    $vals = array_values( $arrCounts );
                    $val = $vals[0];
                    $die = $keys[0] - 48;
                    if ( $val >= 4 )
                    {
                        $score = $die * $val;
                        $score = $weighted ? $score + 200 : $score;
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
