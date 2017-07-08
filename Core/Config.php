<?php

namespace DryMile\Core;

// Define our helpers
define("CORE_DIR", __DIR__.'/' );
define("CORE_VER_MAJOR", '0' );
define("CORE_VER_MINOR", '1' );
define("CORE_VER", CORE_VER_MAJOR . '.' . CORE_VER_MINOR );

// Bring in our key requirements
require_once CORE_DIR . "Debug.php";
require_once CORE_DIR . "App.php";
require_once CORE_DIR . "Responder.php";

