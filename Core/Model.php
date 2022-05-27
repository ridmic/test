<?php namespace DryMile\Core;

include_once "Utils/Object.php";
include_once "Debug.php";

class MysqliModel extends Utils\ObjectX
{
    protected $app          = null;
    protected $name         = '-unknown-';
    protected $connection   = null;
    protected $dbConfig     = null;
    protected $mysqli_errno = 0;
    protected $mysqli_error = '';
    protected $mode         = 'reader';
    protected $level        = 0;
    protected $connectStack = [];

    public function __construct( App $app, $name = '-unknown-' )  
    {
        parent::__construct();
    
        $this->app  = $app;
        $this->name = $name;

        // Pull in our config
        $this->dbConfig = new Utils\Config;
        $this->dbConfig->loadConfig( $this->app->pathToConfig('database') );
        
        \mysqli_report(MYSQLI_REPORT_STRICT);
    }
    public function name()      { return $this->name; }
    
    protected function connectAs( $mode )
    {
        if ( $mode == $this->mode && $this->isConnected() )
            return $this->connection;
            
        $host       = $this->dbConfig->get( "db.$mode.host", '127.0.0.1' );
        $database   = $this->dbConfig->get( "db.$mode.database" );
        $username   = $this->dbConfig->get( "db.$mode.username" );
        $password   = $this->dbConfig->get( "db.$mode.password" );
        $port       = $this->dbConfig->get( "db.$mode.port", 3306 );
        $level      = $this->dbConfig->get( "db.$mode.level", 0 );

        if ( !is_null($host) && !is_null($database) && !is_null($username) && !is_null($password) ) 
        {
            // We can only move up the chain to ensure replication (if used) latency is ignored
            if ( $level > $this->level )
            {
                Debug::debug("Connect = h:$host;d:$database;u:$username;p:$password;x:$port;l:$level" );

                // Create connection
                try 
                {
                    $this->connection = new \mysqli($host, $username, $password, $database, 3306);
                    // Just in case...
                    if ( !$this->connection->connect_error ) 
                    {
                        // Set the character set to utf8 */
                        if ( !$this->connection->set_charset("utf8")) 
                        {
                            Debug::write("Error loading character set utf8: %s\n", $this->connection->error);
                            exit();
                        }
                        $this->mode  = $mode;
                        $this->level = $level;
                        $this->connectStack[] = $this->connection;
                    }
                    else
                    {
                        $this->mysqli_errno = $this->connection->connect_errno;
                        $this->mysqli_error = $this->connection->connect_error;
                        $this->connection = null;
    
                        Debug::debug("Error: [".$this->mysqli_errno."] - ".$this->mysqli_error );
                    }
                } 
                catch (\Exception $e ) 
                {
                    $this->connection = null;

                    Debug::debug("Error: [".$e->getCode()."] - ".$e->getMessage() );
                }                
            }
            return $this->connection;
        }
        return null;
    }
    
    public function isConnected()   { return !is_null($this->connection); }
    
    protected function disconnect()
    {
        if ( count($this->connectStack) )
        {
            foreach ( $this->connectStack as $connection )
            {
                if ( !is_null($connection) )
                    mysqli_close($connection);
            }
            $this->connectStack = [];
        }
    }
}
