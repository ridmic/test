<?php

namespace DryMile\Core\Utils\Test;

use DryMile\Core\Utils as Utils;

require_once __DIR__ .'/../UnitTest.php';
require_once __DIR__ .'/../Config.php';

class Config_Test extends Utils\UnitTest 
{
    // Load our test ini file
	public function set_up() {
		$this->config = new Utils\Config();
		$this->configPath  = __DIR__.'/Config.ini';
		$this->configPath2 = __DIR__.'/ConfigX.ini';
	}
	
    // Test loading
	public function test_load_config_true()        { $this->assert_true( $this->config->loadConfig($this->configPath), 'No Config: '.$this->configPath ); }
	public function test_load_config_false()       { $this->assert_false( $this->config->loadConfig($this->configPath2), 'Found Config: '.$this->configPath2 ); }
	public function test_load_config_reload()      { $this->assert_true( $this->config->loadConfig($this->configPath), 'No Config: '.$this->configPath ); }

    // Test Global Access
    /*
    name = database
    user = user
    password = pass
    */
	public function test_get_global()        
	{ 
	    $this->assert_equal( $this->config->get('name1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('name', 'xx'), 'database' );

	    $this->assert_equal( $this->config->get('user1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('user', 'xx'), 'user' );

	    $this->assert_equal( $this->config->get('password1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('password', 'xx'), 'pass' );
	}
    
    // Test Namespace Access
    /*
    [db]
    name = mydatabase
    user = myuser
    password = mypass
    */
	public function test_get_namespace()        
	{ 
	    $this->assert_equal( $this->config->get('db.name1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.name', 'xx'), 'mydatabase' );

	    $this->assert_equal( $this->config->get('db.user1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.user', 'xx'), 'myuser' );

	    $this->assert_equal( $this->config->get('db.password1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.password', 'xx'), 'mypass' );
	}
    
    // Test sub Namespace Access
    /*
    [db.development]
    user = root
    password = mypass-dev
    
    [db.production]
    password = mypass-prod
    
    [db.production.root]
    password = mypass-root
    */
	public function test_get_sub_namespaces()        
	{ 
	    // db.development
	    $this->assert_equal( $this->config->get('db.development.name1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.development.name', 'xx'), 'mydatabase' );
	    $this->assert_equal( $this->config->get('db.development.user1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.development.user', 'xx'), 'root' );
	    $this->assert_equal( $this->config->get('db.development.password1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.development.password', 'xx'), 'mypass-dev' );

	    // db.production
	    $this->assert_equal( $this->config->get('db.production.name1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.production.name', 'xx'), 'mydatabase' );
	    $this->assert_equal( $this->config->get('db.production.user1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.production.user', 'xx'), 'myuser' );
	    $this->assert_equal( $this->config->get('db.production.password1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.production.password', 'xx'), 'mypass-prod' );

	    // db.production.root
	    $this->assert_equal( $this->config->get('db.production.root.name1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.production.root.name', 'xx'), 'mydatabase' );
	    $this->assert_equal( $this->config->get('db.production.root.user1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.production.root.user', 'xx'), 'myuser' );
	    $this->assert_equal( $this->config->get('db.production.root.password1', 'xx'), 'xx' );
	    $this->assert_equal( $this->config->get('db.production.root.password', 'xx'), 'mypass-root' );
	}
}
Utils\UnitTest::test();