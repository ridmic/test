<?php

// Run all of the tests in this folder
$path = __DIR__;
foreach(glob($path. "/*.php") as $file) 
{
    if ( basename($file) !=basename( __FILE__ ) ) 
    {
        echo "==================================================================\n";
        echo "TESTING: ". basename($file)."\n";
        echo "==================================================================\n";
        include $file;
    }
}
