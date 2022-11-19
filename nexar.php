<?php

require('scan2id.php');
require('nexarAPI.php');
require('header.php');
require('mysqlConn.php');

printHeader("Nexar");

?>
<form action="nexar.php" method="post">
    <input name="q" type="text" value=""/>&nbsp;
    <input class="twohndrdpx" name="submit" type="submit" value="Zoek"/>
</form>
<?php
if (isset($_POST["q"])) {
    $txt = $_POST['q'];
    $input = scan2id(validateInput($_POST["q"]));
    $nexarData = json_decode(nexarQuery($input), true);
    echo("<br />\n");
    echo("<pre>\n");
    print_r($nexarData);
    echo("</pre>\n");
}

printFooter();
