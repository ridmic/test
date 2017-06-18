<?php
//include "header.php";

require_once __DIR__ . "/../core/app.php";

use Ridmic\Core as Core;

Core\Debug::level( Core\Debug::DBG_DEBUG );
Core\Debug::showDateTime( false );

Core\Debug::write('START');
 
$myApp = Core\AppFactory::buildMvc( 'mike' );
$myApp->init();
$myApp->run();

Core\Debug::write('END');

//include "footer.php";
?>
