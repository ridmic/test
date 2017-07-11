<?php
namespace DryMile\Core;

include_once "Debug.php";

class Object
{
    function __construct()
    {
    }
    
    function __destruct()
    {
    }
    
    public function __toString()
    {
      return "Object(".get_class($this).")";
    }
    
    // Converts url name (part1_part2) into a class name (Part1Part2)
    public static function toClassName( $name )
    {
        $name = ucwords(str_replace( '_', ' ', $name ));
        $name = str_replace( ' ', '', $name );
        return $name;
    }
    
    // Converts a class name (Part1Part2) into a  url name (part1_part2)  
    public static function toURLName( $name )
    {
        $pieces = preg_split('/(?=[A-Z])/',$name);
        $name   = implode( ' ', $pieces );
        $name   = strtolower(str_replace( ' ', '_', trim($name) ));
        return $name;
    }
    
}
