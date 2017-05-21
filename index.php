<?php
include "header.php";
include "core/object.php";

use Ridmic\Core as Core;

class Test extends Core\Object
{
  function foo($a, $b, $c, $d, $e )
  {  
    $this->traceFunction();
  }
}

Core\Object::defDebugLevel( Core\Object::DBG_TRACE );
Core\Object::defShowClass( false );

$xObj = new Core\Object();
$xObj->debug( "Hello" );

$x = new Test();
$x->foo("aaa", 1, true, array('a' => 1,'b' => 2,'c' => 4), $x );
  
include "footer.php";
?>
