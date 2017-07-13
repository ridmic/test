<?php use DryMile\Core\Utils as Utils;

// Pull in our framework config
require __DIR__ . "/../Core/Config.php";
require_once CORE_DIR .'/Utils/Logger.php';

$logger = new Utils\HtmlLogger();

// Pull in our suites
include CORE_DIR . '/Utils/Tests/_TestSuite.php';
