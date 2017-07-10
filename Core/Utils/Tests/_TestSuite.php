<?php

use DryMile\Core\Utils as Utils;

require_once __DIR__ .'/../UnitTest.php';

// Run all of the tests in this folder
$path = __DIR__;

foreach(glob($path. "/*.php") as $file) 
{
    if ( basename($file) !=basename( __FILE__ ) ) 
    {
        echo "=======================================\n";
        echo "TESTING: ". basename($file)."\n";
        echo "=======================================\n";

        Utils\UnitTest::$noRun = true;
        
        include $file;
        
        Utils\UnitTest::$noRun = false;
        Utils\UnitTest::test("DryMile\Core\Utils\Test\\".basename($file, '.php')."_Test");
    }
}
