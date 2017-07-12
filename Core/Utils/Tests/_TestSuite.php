<?php

use DryMile\Core\Utils as Utils;

require_once __DIR__ .'/../UnitTest.php';
require_once __DIR__ .'/../Logger.php';

// Run all of the tests in this folder
$path = __DIR__;

$logger = new Utils\ConsoleLogger();
$logger->timestamp( false );

Utils\UnitTest::$logger = $logger;
foreach(glob($path. "/*.php") as $file) 
{
    if ( basename($file) !=basename( __FILE__ ) ) 
    {
        $logger->write( "=======================================" );
        $logger->write( "TESTING: ". basename($file) );
        $logger->write( "=======================================" );

        Utils\UnitTest::$noRun = true;
        
        include $file;
        
        Utils\UnitTest::$noRun = false;
        Utils\UnitTest::test("DryMile\Core\Utils\Test\\".basename($file, '.php')."_Test");
    }
}
