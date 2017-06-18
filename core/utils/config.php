<?php namespace Ridmic\Core\Utils;

class Config
{
    protected $defaultNamespace = 'global';
    protected $config           = null;
    protected $filename         = '';
    
    function __construct()
    {
        
    }
    
    public function get( $index, $default = null )
    {
        $bits       = explode( '.', $index );
        $namespace  = $this->defaultNamespace; 
        $result     = $default;
        if ( count( $bits) > 1 )
        {
            $index      = array_pop( $bits );
            $namespace  = implode( '.', $bits );
        }
        if ( isset($this->config[$namespace][$index] ) )
            $result = $this->config[$namespace][$index];
        return $result;
    }
    
    public function loadConfig( $filename )
    {
        if ( $filename != $this->filename )
        {
            $config = $this->parse_ini_file_extended( $filename );
            if ( $config !== false )
            {
                $this->config   = $config;
                $this->filename = $filename;
                return true;
            }
            return false;
        }
        return true;
    }
    public function reloadConfig()
    {
        return $this->loadConfig( $this->filename );   
    }

    /**
     * Parses INI file adding extends functionality via ".base" postfix on namespace.
     *
     * @param string $filename
     * @return array
     * 
     * 
     * 
     *  [db]
        name = mydatabase
        user = myuser
        password = mypass
        
        [db.development]
        user = root
        password = mypass.dev
        
        [db.production]
        password = mypass.prod
        
        [db.production.root]
        password = mypass.root

     */
    protected function parse_ini_file_extended($filename) 
    {
        $p_ini      = parse_ini_file($filename, true);
        $config     = array();
        $namespaces = array();
        foreach($p_ini as $namespace => $properties)
        {
            // In a namespace/section?
            if ( !is_array($properties) )
            {
                // Push it in the global namespace
                $bits = [$namespace => $properties ];
                $namespace  = $this->defaultNamespace;
                $properties = $bits;
            }

            // inherit any base namespace
            $bits    = explode( '.', $namespace );
            $parent  = '';
            $parents = [];
            while ( !is_null($bit=array_shift($bits)) )
            {
                $parents[] = $bit;
                $parent = implode( '.', $parents );
                if(isset($p_ini[$parent]))
                {
                    foreach($p_ini[$parent] as $prop => $val)
                        $config[$namespace][$prop] = $val;
                }
                // overwrite / set current namespace values
                foreach($properties as $prop => $val)
                    $config[$namespace][$prop] = $val;
            }
        }
        return $config;
    }
}