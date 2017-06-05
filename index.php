<?php
include "header.php";
include "core/object.php";

use Ridmic\Core as Core;

class Test extends Core\Object
{
  function foo($a, $b, $c, $d, $e )
  {  
    $this->traceEnterFunc();
    
    $this->debug( "some text" );

    $this->traceLeaveFunc();
  }
  
  function add1($a, $b )
  {
    $this->traceEnterFunc();

    $this->debug( "some text" );

    return $this->traceLeaveFunc( (int)$a * (int)$b );
  }
  function add2($a, $b )
  {
    $this->traceEnterFunc();
    
    $this->debug( "some text" );
    $this->debug( sprintf("some text: %s or %d", 'ddd', 345 ) );
    
    // Do something
    
    return $this->traceLeaveFunc( (int)$a * (int)$b );
  }
}

Core\Object::defDebugLevel( Core\Object::DBG_TRACE );
Core\Object::defShowDateTime( false );

$xObj = new Core\Object();
$xObj->debug( "Hello" );

$x = new Test();
$x->foo("aaa", 1, true, array('a' => 1,'b' => 2,'c' => 3), $xObj );

$x->add1( 3, 8 );
$x->add2( 3, 8 );

include "footer.php";
?>
