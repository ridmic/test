<?php namespace DryMile\Core\Utils;

class Bits
{
    const   BIT_ALL        = 0xFFFFFFFF;  
    const   BIT_NONE       = 0x00000000;  
    const   BIT_32         = 0x80000000;  
    const   BIT_31         = 0x30000000;  
    const   BIT_30         = 0x20000000;  
    const   BIT_29         = 0x10000000;  
    const   BIT_28         = 0x08000000;
    const   BIT_27         = 0x04000000;
    const   BIT_26         = 0x02000000;
    const   BIT_25         = 0x01000000;
    const   BIT_24         = 0x00800000;
    const   BIT_23         = 0x00400000;
    const   BIT_22         = 0x00200000;
    const   BIT_21         = 0x00100000;
    const   BIT_20         = 0x00080000;
    const   BIT_19         = 0x00040000;
    const   BIT_18         = 0x00020000;
    const   BIT_17         = 0x00010000;
    const   BIT_16         = 0x00008000;
    const   BIT_15         = 0x00004000;  
    const   BIT_14         = 0x00002000;  
    const   BIT_13         = 0x00001000;
    const   BIT_12         = 0x00000800;
    const   BIT_11         = 0x00000400;
    const   BIT_10         = 0x00000200;
    const   BIT_9          = 0x00000100;
    const   BIT_8          = 0x00000080;
    const   BIT_7          = 0x00000040;
    const   BIT_6          = 0x00000020;
    const   BIT_5          = 0x00000010;
    const   BIT_4          = 0x00000008;
    const   BIT_3          = 0x00000004;
    const   BIT_2          = 0x00000002;
    const   BIT_1          = 0x00000001;

    protected   $bits      = self::BIT_NONE;
   
    public function __construct( $bits = self::BIT_NONE )
    {
        if ( is_int($bits) )
            $this->bits = $bits;
    }
   
    public function set( $bits )
    {
        $this->bits = self::BIT_NONE;
        $this->bits = $bits;
        return $this->bits;
    }
    
    public function get()
    {
        return $this->bits;
    }
    
    public function add( $bits )
    {
        $this->bits |= $bits;
        return $this->bits;
    }
    public function clear( $bits = null )
    {
        if ( is_null( $bits ) )
            $this->bits = self::BIT_NONE;
        else
            $this->bits &= ~$bits;
        return $this->bits;
    }
    public function toggle( $bits = null )
    {
        if ( is_null( $bits ) )
            $this->bits = ~$this->bits;
        else
            $this->bits ^= $bits;
        return $this->bits;
    }

    public function isOnlyBits( $bits )
    {
        $mask = $bits | ~$this->bits;
        return $mask > 0 ? true : false;
    }

    public function anyBits( $bits )
    {
        $mask = $bits & $this->bits;
        return $mask > 0 ? true : false;
    }

    public function allBits( $bits )
    {
        $mask = $bits & $this->bits;
        return $mask == $bits ? true : false;
    }

    // Magic Functions
    public function __toString()
    {
        $mask = self::BIT_32;
        $str  = [];
        do
        {
            $str[] = $mask & $this->bits ? '1' : '0';
            $mask  = $mask >> 1;
        }while( $mask > 0 );
        return implode( '', $str);
    }      
}

class BitSlots extends Bits
{
    const OFFSET_MAX        = 32;
    const MASK_DIVIDER      = '|';
    
    protected $slotCount    = 0;
    protected $slots        = array();
    protected $slotOffsets  = array();
    protected $slotLengths  = array();
    protected $slotMasks    = array();
 
    public function __construct(  $bits = self::BIT_NONE )
    {
        parent::__construct( $bits );
    }
    
    public function getSlotCount()  { return $this->slotCount; }
    public function showMask()      { return strrev(implode( self::MASK_DIVIDER, $this->slots )); }
    
    public function setMask( $mask = '' )
    {
        $this->bits      = 0;
        $this->slots     = explode( self::MASK_DIVIDER, strrev($mask) );
        $this->slotCount = count( $this->slots );
        $offset          = 0;
        $maskBits        = 0;
        foreach( $this->slots as $slot )
        {
            $this->slotOffsets[] = $offset;
            $this->slotLengths[] = strlen( $slot );
            $this->slotMasks[]   = pow(2, strlen( $slot ))-1;
            $offset += strlen( $slot );
        }
        // Validate
        if ( $offset > self::OFFSET_MAX )
            $this->resetMask();
        return $offset <= self::OFFSET_MAX;
    }
    
    public function resetMask()
    {
        $this->slotCount    = 0;
        $this->slotOffsets  = array();
        $this->slotLengths  = array();
        $this->slotMasks    = array();
        $this->slotValue    = 0;
    }
    
    public function setSlot( $slot, $value, $clean = true )
    {
        // Initialise
        $setSlot = false;
        $slot    = intval($slot);
        $value   = intval($value);
        
        // Validate
        if ( $slot >= 0 && $slot < $this->slotCount )
        {
            if ( $value >= 0 && $value <= $this->slotMasks[$slot] )
            {
                // Set the appropriate bits
                if ( $clean )
                    $this->clear( $this->slotMasks[$slot] << $this->slotOffsets[$slot] );
                $this->add( $value << $this->slotOffsets[$slot] );
                $setSlot = true;
            }
        }
        return $setSlot;
    }

    public function getSlot( $slot )
    {
        // Initialise
        $slot  = intval($slot);
        $value = 0;
        
        // Validate
        if ( $slot >= 0 && $slot < $this->slotCount)
        {
            // Get the appropriate bits
            $value = ($this->get() >> $this->slotOffsets[$slot]) & $this->slotMasks[$slot];
        }
        return $value;
    }
    
    public function showSlots()
    {
        $strValue = strrev("".$this);
        $arrSlots = [];
        for ( $i = 0 ; $i < $this->slotCount ; $i++ )
            $arrSlots[] = substr( $strValue, $this->slotOffsets[$i], $this->slotLengths[$i]);
        return strrev(implode( self::MASK_DIVIDER, $arrSlots ));
    }
}

