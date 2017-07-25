<?php namespace DryMile\Mike;

require_once CORE_DIR . "Model.php";

use DryMile\Core as Core;

class TestModel extends Core\MysqliModel
{

    public function getAs( $type )
    {
        $this->connectAs($type);
    }
    
}
