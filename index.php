<html>
    <head>
        
    </head>
    <body>
        <h1>Test Site</h1>
        <?php
            include "core/object.php";
            xobject::defDebugLevel(1);

            
            $xObj = new xObject();
            $xObj->debug( "Hello Emily 4" );
        ?>
    </body>
</html>