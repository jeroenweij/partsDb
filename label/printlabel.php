<?php
if (!isset($_POST["id"]) || !isset($_POST["mpn"]) || !isset($_POST["loc"])) {
    //header("Location: ../index.php");
    //exit();
    echo "NOK!";
}

$id = $_POST["id"];
$mpn = $_POST["mpn"];
$type = $_POST["type"];
$value = $_POST["value"];
$loc = $_POST["loc"];

$value = str_replace(array("Ω", "µ"), array("Ohm", "u"), $value);

$command = "./printlabel.sh $id \"$mpn\" \"$type\" \"$value\" \"$loc\"";
$output = null;
$retval = null;
exec($command, $output, $retval);

$filename = "output.pdf";
if ($retval != 0 || !file_exists($filename)) {
    echo("<pre>\n");
    echo("$command\n");
    echo("Returned with status $retval\n and output:\n\n");
    print_r($output);
    echo("</pre>");
} else {
    header("Location: $filename");
}
?>