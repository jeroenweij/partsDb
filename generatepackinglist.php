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
    <title>Pakbon</title>
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
        
        .nostyle {
            border-bottom: 0px;
            padding: 0px;
            text-align: left;
        }
    </style>
</head>
<body>
<div style='float: right'> <img style='width:200px' src='extimg/" . $row["logo"] . "'></div>
    <h1>Pakbon</h1>
    <b>" . $row["relationname"] . "</b>
    <p>" . $row["contact"] . "<br>\n" . str_replace("\n", "<br>\n", $row["relationaddress"]) . "</p>
    <table class='nostyle'>
        <tr>
            <td class='nostyle' style='width: 120px'>Pakbon</td>
            <td class='nostyle' style='width: 300px'>" . date("ym") . sprintf('%04d', $id)."</td>
            <td class='nostyle'>" . $row["companyname"] . "</td>
        </tr>
        <tr>
            <td class='nostyle'>Datum</td>
            <td class='nostyle'>" . date("d-m-Y") . "</td>
            <td class='nostyle' rowspan='3'>" . str_replace("\n", "<br>\n", $row["companyaddress"]) . "</td>
        </tr>
        <tr>
            <td class='nostyle'>Order</td>
            <td class='nostyle'>" . $row["name"] . "</td>
        </tr>
        <tr>
            <td class='nostyle'>&nbsp;</td>
            <td class='nostyle'></td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th style='text-align: left'>#</th>
                <th style='text-align: left'>Aantal</th>
                <th style='text-align: left'>Beschrijving</th>
            </tr>
        </thead>
        <tbody>";

// List parts
$sql = "SELECT parts.name, orderpart.count, orderpart.packed
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.count > 0 AND parts.deleted=0 AND orderpart.orderId=$id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $i=1;
    while ($prow = $result->fetch_assoc()) {
        $html=$html."<tr>\n";
        $html=$html."<td>" . $i . "</td>\n";
        $html=$html."<td>" . $prow["count"] . "</td>\n";
        $html=$html."<td>" . $prow["name"] . "</td>\n";
        $html=$html."</tr>\n";
        $i++;
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