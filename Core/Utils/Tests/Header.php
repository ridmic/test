<?php

namespace DryMile\Core\Utils\Test;

use DryMile\Core\Utils as Utils;

require_once __DIR__ .'/../UnitTest.php';
require_once __DIR__ .'/../Header.php';

class Header_Test extends Utils\UnitTest 
{
    // Load our test ini file
	public function set_up() {
		$this->headerObj = new Utils\Header();
	}
	
	public function test_one_accept_type() 
	{ 
	    $testing = 'text/html';
        $_SERVER['HTTP_ACCEPT'] = $testing;
        $result = $this->headerObj->getBestSupportedMimeType();
        
	    $this->assert_not_empty( $result );
	    $this->assert_equal( count($result), 1 );

        $result = array_keys($result );

	    $this->assert_not_empty( $result );
	    $this->assert_equal( count($result), 1 );
	    $this->assert_equal( $result[0], $testing );
	}
	
	public function test_two_accept_types1() 
	{ 
	    $testing = 'text/html;q=0.9, text/xhtml;q=0.5';
        $_SERVER['HTTP_ACCEPT'] = $testing;
        $result = $this->headerObj->getBestSupportedMimeType();
        
	    $this->assert_not_empty( $result );
	    $this->assert_equal( count($result), 2 );

        $result = array_keys($result );

	    $this->assert_not_empty( $result );
	    $this->assert_equal( count($result), 2 );
	    $this->assert_equal( $result[0], 'text/html' );
	    $this->assert_equal( $result[1], 'text/xhtml' );
	}	
	
	public function test_two_accept_types2() 
	{ 
	    $testing = 'text/html;q=0.5, text/xhtml;q=0.9';
        $_SERVER['HTTP_ACCEPT'] = $testing;
        $result = $this->headerObj->getBestSupportedMimeType();
        
	    $this->assert_not_empty( $result );
	    $this->assert_equal( count($result), 2 );

        $result = array_keys($result );

	    $this->assert_not_empty( $result );
	    $this->assert_equal( count($result), 2 );
	    $this->assert_equal( $result[0], 'text/xhtml' );
	    $this->assert_equal( $result[1], 'text/html' );
	}	
}
Utils\UnitTest::test();