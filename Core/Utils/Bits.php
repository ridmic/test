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
        $this->bits = $flags;
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
        return (string)"FLAGS [".implode( '', $str)."]<br>\n";
    }      
}
