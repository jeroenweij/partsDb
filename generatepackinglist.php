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

$sql = "SELECT orders.name, orders.relation, companys.logo, relations.contact,
        companys.address as companyaddress, 
        relations.address as relationaddress,
        companys.name as companyname,
        relations.name as relationname
        FROM orders 
        LEFT JOIN companys ON companys.id=orders.company 
        LEFT JOIN relations ON relations.id=orders.relation 
        WHERE orders.id='$id'";
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
<div style='float: right'> <img style='width:200px' src='extimg/" . $row["logo"] . "'></div>
    <h1>Pakbon</h1>
    <b>" . $row["companyname"] . "</b>
    <pre>" . $row["companyaddress"] . "</pre>
    <b>" . $row["relationname"] . "</b>
    <pre>" . $row["contact"] . "\n" . $row["relationaddress"] . "</pre>
    <table>
        <thead>
            <tr>
                <th style='text-align: left'>Component</th>
                <th style='text-align: left'>Aantal nodig</th>
                <th style='text-align: left'>Bij julie op voorraad</th>
                <th style='text-align: left'>In deze zending</th>
            </tr>
        </thead>
        <tbody>";

// List parts
$sql = "SELECT parts.name, orderpart.count, orderpart.packed, 
        (SELECT SUM(count) FROM extstock WHERE extstock.part=parts.id AND extstock.relation=" . $row["relation"] . ") as extstock
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.count > 0 AND parts.deleted=0 AND orderpart.orderId=$id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($prow = $result->fetch_assoc()) {
        $html=$html."<tr>\n";
        $html=$html."<td>" . $prow["name"] . "</td>\n";
        $html=$html."<td>" . $prow["count"] . "</td>\n";
        $html=$html."<td>" . $prow["extstock"] . "</td>\n";
        $html=$html."<td>" . $prow["packed"] . "</td>\n";
        $html=$html."</tr>\n";
    }
}

$html=$html."</tbody></table>";
$html=$html."</body></html>";

$dompdf->loadHtml($html);

/**
 * Create the PDF and set attributes
 */
$dompdf->render();

$dompdf->addInfo("Title", "Pakbon"); // "add_info" in earlier versions of Dompdf

/**
 * Send the PDF to the browser
 */
$dompdf->stream("Picklijst.pdf", ["Attachment" => 0]);
//echo($html);