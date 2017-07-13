<?php

use DryMile\Core\Utils as Utils;

require_once __DIR__ .'/../UnitTest.php';
require_once __DIR__ .'/../Logger.php';

// Run all of the tests in this folder
$path = __DIR__;

if ( !isset($logger) )
    $logger = new Utils\ConsoleLogger();
$logger->timestamp( false );
$logger->writeHeading_H1( "TEST SUITE: ". basename( __FILE__ ) );


Utils\UnitTest::$logger = $logger;
foreach(glob($path. "/*.php") as $file) 
{
    if ( basename($file) !=basename( __FILE__ ) ) 
    {
        $logger->writeHeading_H2( "TESTING: ". basename($file) );

        Utils\UnitTest::$noRun = true;
        
        include $file;
        
        Utils\UnitTest::$noRun = false;
        Utils\UnitTest::test("DryMile\Core\Utils\Test\\".basename($file, '.php')."_Test");
    }
}
