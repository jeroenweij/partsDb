<?php
if (!isset($_POST["id"]) && !isset($_GET["id"])) {
    header("Location: index.php");
    exit();
}
require('mysqlConn.php');
require('header.php');
require('formFunctions.php');
$id = "";
if (isset($_POST["id"])) {
    $id = $_POST["id"];
} else {
    $id = $_GET["id"];
}

if (isset($_POST["relation"])) {
    $relation = validateNumberInput($_POST["relation"]);
    // List parts
    $sql = "SELECT parts.id, parts.name, orderpart.count, orderpart.packed, 
        (SELECT SUM(count) FROM extstock WHERE extstock.part=parts.id AND extstock.relation=$relation) as extstock
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.count > 0 AND parts.deleted=0 AND orderpart.orderId=$id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($prow = $result->fetch_assoc()) {
            $pid = $prow["id"];

            $stockresult = $conn->query("Select stock.id, count FROM stock WHERE stock.deleted=0 AND stock.partId=$pid;");
            if ($stockresult && $stockresult->num_rows > 0) {
                while ($stockrow = $stockresult->fetch_assoc()) {
                    $new = "";
                    if (isset($_POST["newstock-". $stockrow["id"] . "-$pid"])) {
                        $new = validateNumberInput($_POST["newstock-". $stockrow["id"] . "-$pid"]);
                        $new += $stockrow["count"];
                    }
                    if (strlen($new) > 0) {
                        $conn->query("UPDATE stock SET count=$new WHERE id=". $stockrow["id"] . ";");
                    }
                }
            }


            $newext = "";
            if (isset($_POST["newstock-$pid"])) {
                $newext = validateNumberInput($_POST["newextstock-$pid"]);
            }
            if (strlen($newext) > 0) {
                $conn->query("INSERT INTO extstock (part, relation, count) SELECT $pid, $relation, 0
                                    FROM DUAL WHERE NOT EXISTS (
                                    SELECT count FROM extstock WHERE part = $pid AND relation = $relation);");
                $conn->query("UPDATE extstock SET count=$new WHERE part=$pid AND relation=$relation;");
            }
        }
    }
    $conn->query("UPDATE orders SET status=4 WHERE id=$id;");

    header("Location: order.php?id=$id");
}

$losspercentage = 2;
if (isset($_POST["losspercentage"])) {
    $losspercentage = validateNumberInput($_POST["losspercentage"]);
}
$lossminimum = 1;
if (isset($_POST["lossminimum"])) {
    $lossminimum = validateNumberInput($_POST["lossminimum"]);
}

$sql = "SELECT orders.id, orders.name, orders.relation
        FROM orders 
        WHERE orders.id='$id'";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    printHeader($row["name"]);

    ?>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo($id); ?>"/>
        <table>
            <tr>
                <td>Verliespercentage:</td>
                <td>
                    <input type="text" value="<?php echo($losspercentage); ?>" name="losspercentage"/>
                </td>
            </tr>
            <tr>
                <td>Minimum verlies:</td>
                <td>
                    <input type="text" value="<?php echo($lossminimum); ?>" name="lossminimum"/>
                </td>
            </tr>
        </table>
        <input name="next" type="submit" value="Update"/>
    </form>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo($id); ?>"/>
        <input type="hidden" name="relation" value="<?php echo($row["relation"]); ?>"/>
        <?php
        // List parts
        $sql = "SELECT parts.id, parts.name, orderpart.count, orderpart.packed, 
        (SELECT SUM(count) FROM extstock WHERE extstock.part=parts.id AND extstock.relation=" . $row["relation"] . ") as extstock
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.count > 0 AND parts.deleted=0 AND orderpart.orderId=$id ORDER BY parts.id ASC";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            ?>

            <div>
                <h3>Componenten</h3>
                <table class="styled-table">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Component</th>
                        <th>Externe voorraad</th>
                        <th>Verstuurd</th>
                        <th>Verbruikt</th>
                        <th>Verlies</th>
                        <th>Externe voorraad</th>
                        <th>Teruggekomen</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    while ($prow = $result->fetch_assoc()) {
                        $pid = $prow["id"];
                        $loss = max(round($prow["count"] * $losspercentage / 100), $lossminimum);
                        $new = max($prow["extstock"] + $prow["packed"] - ($prow["count"] + $loss), 0);
                        echo("<tr>\n");
                        echo("<td><a href='item.php?id=$pid'>$pid</a></td>\n");
                        echo("<td><a href='item.php?id=$pid'>" . $prow["name"] . "</a></td>\n");
                        echo("<td>" . $prow["extstock"] . "</td>\n");
                        echo("<td>" . $prow["packed"] . "</td>\n");
                        echo("<td>" . $prow["count"] . "</td>\n");
                        echo("<td>$loss</td>\n");
                        echo("<td><input type='text' name='newextstock-$pid' /></td>\n");


                        $stockresult = $conn->query("Select stock.id, locations.name, sublocation FROM stock LEFT JOIN locations ON stock.location = locations.id WHERE stock.deleted=0 AND stock.partId=$pid;");
                        if ($stockresult && $stockresult->num_rows > 0) {
                            echo("<td><table>\n");
                            while ($stockrow = $stockresult->fetch_assoc()) {
                                echo("<tr>\n");
                                echo("<td>". $stockrow["name"] . "-". $stockrow["sublocation"] . "</td>\n");
                                echo("<td><input type='text' value='$new' name='newstock-". $stockrow["id"] . "-$pid' /></td>\n");
                                echo("</tr>\n");
                            }
                            echo("</table></td>\n");

                        } else {
                            echo("<td>geen lokatie</td>\n");
                        }

                        echo("</tr>\n");
                    }

                    ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ?>
        <input name="save" type="submit" value="Verwerken"/>
    </form>
    <br>
    <form action="order.php" method="post">
        <input type="hidden" name="id" value="<?php echo($id); ?>"/>
        <input name="next" type="submit" value="Annuleren"/>
    </form>
    <?php
    printFooter();
} else {
    // Invalid id
    header("Location: index.php");
    exit();
}
