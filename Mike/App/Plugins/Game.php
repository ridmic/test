<?php namespace DryMile\Mike;

require_once 'Turn.php';

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
    public function scoreGameAsXml()        { return $this->arrayToXML( $this->scoreGame(), new \SimpleXMLElement('<score/>'), 'xxx' ); }
    
    public function arrayToXML($array, \SimpleXMLElement $xml, $child_name)
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
