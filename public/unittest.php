<?php use DryMile\Core\Utils as Utils;

// Pull in our framework config
require __DIR__ . "/../Core/Config.php";
require_once CORE_DIR .'/Utils/Logger.php';

// Pull in our suites
?>
<html>
    <header></header>
    <body>
        <pre>
<?php include CORE_DIR . '/Utils/Tests/_TestSuite.php'; ?>
        </pre>
    </body>
</html>