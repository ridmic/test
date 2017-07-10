<?php

namespace DryMile\Core\Utils\Test;

use DryMile\Core\Utils as Utils;

require_once __DIR__ .'/../UnitTest.php';

class UnitTest_Test extends Utils\UnitTest 
{
	public function set_up() {
		$this->test = new Utils\UnitTest( true );
	}

	public function test_assert_true_true()                 { $this->assert_true( $this->test->assert_true(TRUE) ); }
	public function test_assert_true_false()                { $this->assert_false( $this->test->assert_true(FALSE) ); }
	
	public function test_assert_false_false()               { $this->assert_true( $this->test->assert_false(FALSE) ); }
	public function test_assert_false_true()                { $this->assert_false( $this->test->assert_false(TRUE) ); }
	
	public function test_assert_equal_true()                { $this->assert_true( $this->test->assert_equal(1, 1) ); }
	public function test_assert_equal_false()               { $this->assert_false( $this->test->assert_equal(1, 2) ); }
	
	public function test_assert_not_equal_true()            { $this->assert_false( $this->test->assert_not_equal(1, 1) ); }
	public function test_assert_not_equal_false()           { $this->assert_true( $this->test->assert_not_equal(1, 2) ); }

	public function test_assert_equivalent_true()           { $this->assert_true( $this->test->assert_equivalent(true, true) ); 
	                                                          $this->assert_true( $this->test->assert_equivalent(false, false) ); }
	public function test_assert_equivalent_false()          { $this->assert_false( $this->test->assert_equivalent(1, true) );
	                                                          $this->assert_false( $this->test->assert_equivalent(0, false) ); }
	
	public function test_assert_type_true()                 { $this->assert_true( $this->test->assert_type(1, 'integer') ); }
	public function test_assert_type_false()                { $this->assert_false( $this->test->assert_type(1, 'bool') ); }
	
	public function test_assert_class_true()                { $this->assert_true( $this->test->assert_class($this, 'DryMile\Core\Utils\Test\UnitTest_Test') ); }
	public function test_assert_class_false()               { $this->assert_false( $this->test->assert_class($this, 'UnitTest_TestX') ); }
	
	public function test_assert_empty_true()                { $this->assert_true( $this->test->assert_empty([]) ); }
	public function test_assert_empty_false()               { $this->assert_false( $this->test->assert_empty([1,2]) ); }
	
	public function test_assert_not_empty_true()            { $this->assert_true( $this->test->assert_not_empty([1,2]) ); }
	public function test_assert_not_empty_false()           { $this->assert_false( $this->test->assert_not_empty([]) ); }
	
	public function test_assert_has_key_true()              { $this->assert_true( $this->test->assert_has_key(['a' => '1'], 'a') ); }
	public function test_assert_has_key_false()             { $this->assert_false( $this->test->assert_has_key(['a' => '1'], 'b') ); }

	public function test_assert_does_not_have_key_true()    { $this->assert_true( $this->test->assert_does_not_have_key(['a' => '1'], 'b') ); }
	public function test_assert_does_not_have_key_false()   { $this->assert_false( $this->test->assert_does_not_have_key(['a' => '1'], 'a') ); }

}
Utils\UnitTest::test();