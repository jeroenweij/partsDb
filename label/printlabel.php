<?php
if (!isset($_POST["id"]) || !isset($_POST["mpn"]) || !isset($_POST["loc"])) {
    header("Location: ../index.php");
    exit();
}

$id = $_POST["id"];
$mpn = $_POST["mpn"];
$type = $_POST["type"];
$value = $_POST["value"];
$package = $_POST["package"];
$loc = $_POST["loc"];

$value = str_replace(array("Ω", "µ", "°"), array("Ohm", "u", ""), $value);
if (strlen(trim($loc)) < 2) $loc = "";
if (strlen(trim($package)) < 2) $package = "";
if (str_ends_with($value, "-")) $value = "";

$bs = "?"; //  barcode seperator
$barcode = "EA{$id}{$bs}1P{$mpn}{$bs}{$type}{$bs}{$value}{$bs}{$package}{$bs}{$loc}";

$csvdelimiter = "\t";
$datafilename = "data.csv";
$datafile = fopen($datafilename, "w") or die("Unable to open file!");

// Write header
$header = array("id", "mpn", "type", "value", "package", "location", "barcode");
fputcsv($datafile, $header, $csvdelimiter);

// Write data
$data = array($id, $mpn, $type, $value, $package, $loc, $barcode);
fputcsv($datafile, $data, $csvdelimiter);

fclose($datafile);

$command = "glabels-3-batch --input=$datafilename label.glabels";
$output = null;
$retval = null;
exec($command, $output, $retval);
unlink($datafilename);

$filename = "output.pdf";
if ($retval != 0 || !file_exists($filename)) {
    echo("<pre>\n");
    echo("$command\n");
    echo("Returned with status $retval\n and output:\n\n");
    print_r($output);
    echo("</pre>");
} else {
    $printername = getenv('labelprinter', true);
    $command = "lp -d $printername $filename";
    exec($command, $output, $retval);
    unlink($filename);
    if ($retval != 0 || !file_exists($filename)) {
        echo("<pre>\n");
        echo("$command\n");
        echo("Returned with status $retval\n and output:\n\n");
        print_r($output);
        echo("</pre>");
    } else {
        header("Location: $filename");
    }
}
?>
