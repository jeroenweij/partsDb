<?php

if (!isset($_POST["id"])) {
    header("Location: index.php");
    exit();
}

require __DIR__ . "/vendor/autoload.php";
require('mysqlConn.php');

use Dompdf\Dompdf;
use Dompdf\Options;

$id = $_POST["id"];

$sql = "SELECT orders.name, orders.relation FROM orders WHERE orders.id='$id'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
}

$name = "naam";
$quantity = 4;

//$html = '<h1 style="color: green">Example</h1>';
//$html .= "Hello <em>$name</em>";
//$html .= '<img src="example.png">';
//$html .= "Quantity: $quantity";

/**
 * Set the Dompdf options
 */
$options = new Options;
$options->setChroot(__DIR__);

$dompdf = new Dompdf($options);

/**
 * Set the paper size and orientation
 */
$dompdf->setPaper("A4", "portret");

/**
 * Load the HTML and replace placeholders with values from the form
 */
$html = "<!DOCTYPE html>
<html>
<head>
    <title>Picklijst</title>
    <meta charset=\"UTF-8\">
    <link rel=\"stylesheet\" href=\"css/gutenberg.min.css\">
    <style>
table {
    width: 100%;
}
        footer {
    text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>" . $row["name"] . "</h1>
    
    <table>
        <thead>
            <tr>
                <th style='text-align: left'>Component</th>
                <th style='text-align: left'>Aantal nodig</th>
                <th style='text-align: left'>Lokatie</th>
                <th style='text-align: left'>Voorraad</th>
                <th style='text-align: left'>Aantal ingepakt</th>
            </tr>
        </thead>
        <tbody>";

// List parts
$sql = "SELECT parts.id, parts.name, orderpart.count, orderpart.packed, 
        (SELECT SUM(count) FROM stock WHERE stock.partId=parts.id) as stock,
        (SELECT SUM(count) FROM extstock WHERE extstock.part=parts.id AND extstock.relation=" . $row["relation"] . ") as extstock
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.count > 0 AND parts.deleted=0 AND orderpart.orderId=$id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($prow = $result->fetch_assoc()) {
        $pid = $prow["id"];
        $short = max($prow["count"] - ($prow["extstock"] + $prow["packed"]), 0);

        $sql = "SELECT count, sublocation, locations.name as location
                        FROM `stock` 
                        LEFT JOIN locations ON locations.id=stock.location
                        WHERE stock.count>0 AND stock.partId=$pid;";
        $stockresult = $conn->query($sql);

        $rowspan = 1;
        if ($stockresult && $stockresult->num_rows > 0) {
            $rowspan = $stockresult->num_rows;
        }
        $html = $html . "<tr>\n";
        $html = $html . "<td rowspan='$rowspan'><a href='item.php?id=$pid'>" . $prow["name"] . "</a></td>\n";
        $html = $html . "<td rowspan='$rowspan'>$short</td>\n";

        if ($stockresult && $stockresult->num_rows > 0) {
            $addtr = false;
            while ($stockrow = $stockresult->fetch_assoc()) {
                if ($addtr) {
                    $html = $html . "<tr>\n";
                }
                $html = $html . "<td>" . $stockrow["location"] . " " . $stockrow["sublocation"] . "</td>\n";
                $html = $html . "<td>" . $stockrow["count"] . "</td>\n";
                $html = $html . "<td>_____</td>\n";
                $html = $html . "</tr>\n";
                $addtr = true;
            }

        } else {
            $html = $html . "<td>-</td><td>0</td><td>_____</td></tr>";
        }

    }
}

$html = $html . "</tbody></table>";
$html = $html . "</body></html>";

$dompdf->loadHtml($html);

/**
 * Create the PDF and set attributes
 */
$dompdf->render();

$dompdf->addInfo("Title", "Picklijst"); // "add_info" in earlier versions of Dompdf

/**
 * Send the PDF to the browser
 */
$dompdf->stream("Picklijst.pdf", ["Attachment" => 0]);
//echo($html);