<?php namespace DryMile\Core\Utils;


class Singleton
{
    private static $instances = array();
    
    protected function  __construct() {}
    protected function  __clone() {}
    private function    __wakeup() {}

    public static function getInstance()
    {
        $cls = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static;
        }
        return self::$instances[$cls];
    }
}