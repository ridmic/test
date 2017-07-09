<?php

namespace DryMile\Core\Utils;

/* --------------------------------------------------------------
 * UNIT TESTER
 * ------------------------------------------------------------ */
class UnitTest 
{
	/* --------------------------------------------------------------
	 * VARIABLES
	 * ------------------------------------------------------------ */
	
	public $results 		= array();
	public $tests 			= array();
	public $current_test 	= '';
	
	public $start_time 		= 0;
	public $end_time 		= 0;
	public $assertion_count	= 0;
	
	protected $testing      = true;
	
	/* --------------------------------------------------------------
	 * AUTORUNNER
	 * ------------------------------------------------------------ */
	/**
	 * Run your tests!
	 */
	public static function test() 
	{
		// Get all the declared classes
		$classes = get_declared_classes();

		// Loop through them and if they're subclasses of
		// UnitTest then instanciate and run them!
		foreach ($classes as $class) 
		{
			if (is_subclass_of($class, 'DryMile\Core\Utils\UnitTest')) 
			{
				$instance = new $class();
				
				// Only run tests if we have a test_ method
				$methods = get_class_methods($instance);
				$run = FALSE;
				
				foreach ($methods as $method) 
				{
					if (substr($method, 0, 5) == 'test_') 
					{
						$run = TRUE;
					}
				}
				
				if ($run) 
				{
					$instance->run();
				}
			}
		}
	}
	
	/* --------------------------------------------------------------
	 * GENERIC METHODS
	 * ------------------------------------------------------------ */
	
	public function __construct( $testing = false ) 
	{
		$this->testing = $testing;
	}
	
	/**
	 * Record a success
	 */
	public function success() 
	{
		$this->results[$this->current_test]['successes'][] = TRUE;
	}
	
	/**
	 * Record a failure
	 */
	public function failure($message) 
	{
		$this->results[$this->current_test]['failures'][] = $message;
	}
	
	/**
	 * Record an error
	 */
	public function error($message) 
	{
		$this->results[$this->current_test]['errors'][] = $message;
	}
	
	/**
	 * Overload these methods to have code called
	 * before each run
	 */
	public function set_up() { /* Overload */ }
	public function tear_down() { /* Overload */ }
	
	/**
	 * Overload these methods to have code called
	 * before each test
	 */
	public function set_up_test() { /* Overload */ }
	public function tear_down_test() { /* Overload */ }

	/* --------------------------------------------------------------
	 * UNIT TESTING METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Assert that an expression is TRUE boolean. The
	 * base for all the other assertions
	 */
	protected function assert($expression, $message = '' ) 
	{
	    // Testing
	    if ( $this->testing )
	        return (bool)($expression) == TRUE;
	    
		if ((bool)($expression) == TRUE) 
		{
			$this->success();
			return true;
		} 
		else 
		{
            $b = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS ,2);
            $f = count($b) >= 2 ? $b[1]['function'] : '-unknown-';

            $file   = count($b) >= 2 ? basename($b[1]['file']) : '-unknown-';
            $line   = count($b) >= 2 ? $b[1]['line'] : '-unknown-';
            $detail = "{ $file @ $line }";

			$message = ($message) ? $message : $this->_($expression) . " doesn't equate to TRUE";
			$this->failure($message . " @ $detail" );
		}
		return false;
	}
	public function assert_true($e, $m = '') { 
	    return $this->assert($e, $m); 
	}
	
	/**
	 * Assert that the expression is FALSE
	 */
	public function assert_false($expression, $message = '') {
		return $this->assert(!$expression, $message);
	}
	
	/**
	 * Assert that two values are equal ( == )
	 */
	public function assert_equal($one, $two, $message = '') {
		$message = ($message) ? $message : $this->_($one) . " doesn't equal " . $this->_($two);
		return $this->assert(($one == $two), $message);
	}
	
	/**
	 * Assert that two values are not equal ( !== )
	 */
	public function assert_not_equal($one, $two, $message = '') {
		$message = ($message) ? $message : $this->_($one) . " equals " . $this->_($two) . ", and it shouldn't!";
		return $this->assert(($one !== $two), $message);
	}
	
	/**
	 * Assert that two values are equivalent ( === )
	 */
	public function assert_equivalent($one, $two, $message = '') {
		$message = ($message) ? $message : $this->_($one) . " is not equivalent to " . $this->_($two);
		return $this->assert(($one === $two), $message);
	}
	
	/**
	 * Assert that a value is a specific type (using gettype())
	 */
	public function assert_type($value, $type, $message = '') {
		$message = ($message) ? $message : $this->_($value) . " is not the type '" . $type . "'";
		return $this->assert((gettype($value) == $type), $message);
	}
	
	/**
	 * Assert that a value is an instance of a specific class
	 */
	public function assert_class($value, $class, $message = '') {
		$message = ($message) ? $message : $this->_($value) . " is not an instance of the class " . $class;
		return $this->assert((get_class($value) == $class), $message);
	}
	
	/**
	 * Assert that an array is empty
	 */
	public function assert_empty($value, $message = '') {
		$message = ($message) ? $message : $this->_($value) . " is not empty!";
		return $this->assert(empty($value), $message);
	}
	
	/**
	 * Assert that an array is not empty
	 */
	public function assert_not_empty($value, $message = '') {
		$message = ($message) ? $message : $this->_($value) . " is empty!";
		return $this->assert(!empty($value), $message);
	}
	
	/**
	 * Assert that an array has a key
	 */
	public function assert_has_key($array, $key, $message = '') {
		$message = ($message) ? $message : $this->_($array) . " does not have the key " . $this->_($key);
		return $this->assert(isset($array[$key]), $message);
	}
	
	/**
	 * Assert that an array doesn't have a key
	 */
	public function assert_doesnt_have_key($array, $key, $message = '') {
		$message = ($message) ? $message : $this->_($array) . " has the key " . $this->_($key);
		return $this->assert(!isset($array[$key]), $message);
	}
	
	/* --------------------------------------------------------------
	 * TEST RUNNING METHODS
	 * ------------------------------------------------------------ */
	
	public function run() {
		$this->get_tests();
		$this->run_tests();
		$this->print_results();
	}
	
	/**
	 * Loop through all the methods that begin with
	 * test_ and add them to the $this->tests array.
	 */
	public function get_tests() {
		$methods = get_class_methods($this);
		
		foreach ($methods as $method) {
			if (substr($method, 0, 5) == 'test_') {
				$this->tests[] = $method;
			}
		}
	}
	
	/**
	 * Run each test
	 */
	public function run_tests() {
		$this->start_time = microtime(TRUE);
		
		set_error_handler(array($this, 'error_handler'));
		
		$this->set_up();
		foreach ($this->tests as $test) {
			$this->current_test = $test;
    		$this->set_up_test();
			
			try {
				call_user_func_array(array($this, $test), array());
			} catch (Exception $e) {
				if (get_class($e) == 'UnitTestFailure') {
					$this->failure($e->getMessage());
				} else {
					$this->error($e->getMessage());
				}
			}
    		$this->tear_down_test();
		}
		$this->tear_down();
		
		restore_error_handler();
		
		$this->end_time = microtime(TRUE);
	}
	
	/**
	 * Loop through the test results and output them
	 * to the console!
	 */
	public function print_results() {
		$failures = array();
		$errors = array();
		$good = TRUE;
		$i = 1;
		
		// Print out the running status of each method.
		foreach ($this->results as $unit_test => $results) {
			foreach ($results as $result => $values) {
				foreach ($values as $value) {
					$this->assertion_count++;
					
					switch ($result) {
						case 'failures': $this->output('F '); $failures[$unit_test][] = $value; break;
						case 'errors': $this->output('E '); $errors[$unit_test][] = $value; break;
					
						default:
						case 'successes': $this->output('T '); break;
					}
					
					$i++;
					
					if ($i == 20) {
						$this->output("\n");
						$i = 1;
					}
				}
			}
		}
		
		$this->output("\n-------------------------------------\n\n");
		
		// Do we have any failures?
		if ($failures) {
			$good = FALSE;
			
			foreach ($failures as $unit_test => $messages) {
				$this->output("Failures!\n");
				$this->output("=========\n");
				
				$this->output($unit_test . "():\n");
				
				foreach ($messages as $message) {
					$this->output("\t- " . $message."\n");
				}
				
				$this->output("\n");
			}
			
			$this->output("\n-------------------------------------\n\n");
		}
		
		// Do we have any failures?
		if ($errors) {
			$good = FALSE;
			
			foreach ($errors as $unit_test => $messages) {
				$this->output("Errors!\n");
				$this->output("=======\n");
				
				$this->output($unit_test . "():\n");
				
				foreach ($messages as $message) {
					$this->output("\t- " . $message."\n");
				}
			}
			
			$this->output("\n-------------------------------------\n\n");
		}
		
		// Good or bad?
		if ($good) {
			$this->output("\n[Cool! All your tests ran perfectly.\n\n");
		} else {
			$this->output("\n[Not so cool :( there was a problem running your tests!\n\n");
		}
		
		// Finally, test stats
		$this->output("Ran " . 
			 $this->assertion_count . 
			 " assertion(s) in " . 
			 number_format(($this->end_time - $this->start_time), 6) . 
			 " seconds");
		$this->output("\n\n");
	}
	
	/* --------------------------------------------------------------
	 * UTILITY/HELPERS
	 * ------------------------------------------------------------ */
	
	protected function output( $msg )
	{
	    //echo nl2br($msg);
	    echo $msg;
	}
	
	/**
	 * Handle PHP errors
	 */
	public function error_handler($no, $str) {
		$this->error($str);
	}
	
	/**
	 * Format a value and return it as an
	 * output friendly string
	 */
	public function _($value) {
		if (is_null($value)) {
			return '<Null>';
		} elseif (is_bool($value)) {
			return ($value) ? '<TRUE>' : '<FALSE>';
		} elseif (is_array($value) && empty($value)) {
			return '<Empty Array>';
		} elseif (is_array($value) ) {
			return '<Array>';
		} elseif (is_object($value)) {
			return '<Object: ' . get_class($value) .'>';
		} elseif (is_string($value) && empty($value)) {
			return '<Empty String>';
		} else {
			return "<'$value'>";
		}
	}
}
/* --------------------------------------------------------------
 * EXCEPTIONS
 * ------------------------------------------------------------ */

class UnitTestFailure extends \Exception { }
