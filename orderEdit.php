<?php
if (!isset($_POST["id"])) {
    header("Location: index.php");
    exit();
}
require('mysqlConn.php');
require('header.php');
require('formFunctions.php');
$id = "";
if (isset($_POST["id"])) {
    $id = $_POST["id"];
}

function addPart($part, $count)
{
    global $id;
    global $conn;

    $conn->query("INSERT INTO orderpart (orderId, part) SELECT $id, $part  
        FROM DUAL WHERE NOT EXISTS (
        SELECT count FROM orderpart WHERE orderId = $id AND part = $part);");
    $conn->query("UPDATE orderpart SET count = count + $count WHERE orderId = $id AND part = $part;");
}

if (isset($_POST["diff"])) {
    $diff = validateNumberInput($_POST["diff"]);
    $operator = validateInput($_POST["adjust"]);
    $part = validateNumberInput($_POST["partid"]);

    if ($operator == "=") {
        $sql = "UPDATE orderpart SET count=$diff WHERE orderId = $id AND part = $part;";
    } else {
        if (strlen($diff) == 0) {
            $diff = 1;
        }
        $sql = "UPDATE orderpart SET count=GREATEST(count $operator $diff, 0) WHERE orderId = $id AND part = $part;";
    }
    if (strlen($diff) > 0) {
        $conn->query($sql);
    }
}

if (isset($_POST["newpart"])) {
    $newid = validateNumberInput($_POST["partid"]);
    $count = validateNumberInput($_POST["count"]);
    if (strlen($newid) > 0 && strlen(validateInput($_POST["newpart"])) > 0) {
        if (strlen($count) == 0) {
            $count = 1;
        }
        addPart($newid, $count);
    }
}

if (isset($_POST["del-id"])) {
    $delpart = validateNumberInput($_POST["del-id"]);
    $conn->query("DELETE FROM orderpart WHERE orderId = $id AND part = $delpart;");
}

$sql = "SELECT orders.id, orders.name, orders.relation
        FROM orders 
        WHERE orders.id='$id'";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $extra = '<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- jQuery UI library -->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

<script>
$(function() {
    $("#part_input").autocomplete({
        source: "fetchData.php",
        select: function( event, ui ) {
            $("#part_id").val(ui.item.id);
        }
    });
});
</script>';
    printHeader($row["name"], $extra);

    $sql = "SELECT orderproject.count, projects.name, projects.id 
            FROM orderproject 
            LEFT JOIN projects ON orderproject.project = projects.id 
            WHERE count > 0 AND orderproject.orderId=$id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        ?>

        <div>
            <h3>Projecten</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>Project</th>
                    <th>Aantal</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($prow = $result->fetch_assoc()) {
                    echo("<tr>\n");
                    echo("<td>" . $prow["name"] . "</td>\n");
                    echo("<td>" . $prow["count"] . "</td>\n");
                    echo("</tr>\n");
                }

                ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    ?>

    <?php

    // List parts
    $sql = "SELECT parts.id, parts.name, orderpart.count, orderpart.packed, 
        (SELECT SUM(count) FROM stock WHERE stock.partId=parts.id) as stock,
        (SELECT SUM(count) FROM extstock WHERE extstock.part=parts.id AND extstock.relation=" . $row["relation"] . ") as extstock
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE parts.deleted=0 AND orderpart.orderId=$id";
    $result = $conn->query($sql);
    $componentcount = 0;
    if ($result && $result->num_rows > 0) {
        ?>
        <div>
        <h3>Component toevoegen:</h3>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
            <input type="hidden" name="partid" id="part_id"/>
            <input type="text" name="newpart" id="part_input" placeholder="Start typing..."/>
            <input type="text" name="count" placeholder="Aantal"/>
            <input type="submit" value="Opslaan"/>
        </form>
        </div>
        <div>
            <h3>Componenten</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <?php
                    echo("<th>Component</th>");
                    echo("<th>Aantal</th>");
                    echo("<th>Aanpassen</th>");
                    echo("<th>Externe voorraad</th>");
                    echo("<th>Voorraad</th>");
                    echo("<th>Verwijderen</th>");

                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($prow = $result->fetch_assoc()) {
                    $componentcount++;
                    $pid = $prow["id"];
                    $count = $prow["count"];

                    $short = max($count - ($prow["stock"] + $prow["extstock"] + $prow["packed"]), 0);
                    $shortpacked = max($count - ($prow["packed"] + $prow["extstock"]), 0);
                    echo("<tr>\n");
                    echo("<td><a href='item.php?id=$pid'>" . $prow["name"] . "</a></td>\n");
                    echo("<td>$count</td>\n");
                    ?>
                    <td>
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo($id); ?>">
                            <input type="hidden" name="partid" value="<?php echo($pid); ?>">
                            <input style="width: 50px" name="adjust" type="submit" value="-"/>
                            <input style="width: 50px" name="diff" type="text" value=""/>
                            <input style="width: 50px" name="adjust" type="submit" value="="/>
                            <input style="width: 50px" name="adjust" type="submit" value="+"/>
                        </form>
                    </td>
                    <?php
                    echo("<td>" . $prow["extstock"] . "</td>\n");
                    echo("<td>" . $prow["stock"] . "</td>\n");
                    echo("<td><form method=\"post\">\n");
                    echo("<input type=\"hidden\" name=\"id\" value=\"$id\" />\n");
                    echo("<input type=\"hidden\" name=\"del-id\" value=\"$pid\" />\n");
                    echo("<input name=\"delete\" type=\"submit\" value=\"Verwijderen\"  onclick=\"return confirm('Weet je het zeker?')\" />\n");
                    echo("</form></td>\n");
                    echo("</tr>\n");
                }

                ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    ?>
    <br>
    <div>
        <form action="order.php" method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
            <input type="submit" value="Terug naar order"/>
        </form>
    </div>
    <?php


    printFooter();
} else {
    // Invalid id
    header("Location: index.php");
    exit();
}
