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

// Save reults
if (isset($_POST["save"])) {
    $sql = "SELECT parts.id
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.count > 0 AND parts.deleted=0 AND orderpart.orderId=$id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($prow = $result->fetch_assoc()) {
            $pid = $prow["id"];
            if (isset($_POST["pack-c$pid"])) {
                $packed = validateNumberInput($_POST["pack-c$pid"]);
                if ($packed > 0) {
                    $conn->query("UPDATE orderpart SET packed=packed+$packed WHERE orderId=$id AND part=$pid");
                }
            } else {
                $sql = "SELECT stock.id, stock.count FROM `stock` WHERE stock.count>0 AND stock.partId=$pid;";
                $stockresult = $conn->query($sql);
                if ($stockresult && $stockresult->num_rows > 0) {
                    while ($stockrow = $stockresult->fetch_assoc()) {
                        $name = "pack-" . $stockrow["id"];
                        if (isset($_POST[$name])) {
                            $packed = validateNumberInput($_POST[$name]);
                            if ($packed > 0) {
                                $conn->query("UPDATE orderpart SET packed=packed+$packed WHERE orderId=$id AND part=$pid");
                                $newstock = max($stockrow["count"] - $packed, 0);
                                $conn->query("UPDATE stock SET count=$newstock WHERE id=" . $stockrow["id"]);
                            }
                        }
                    }
                }
            }
        }
    }

    header("Location: order.php?id=$id");
    exit();
}

$sql = "SELECT orders.name, orders.relation FROM orders WHERE orders.id='$id'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    printHeader("Inpakken: " . $row["name"]);

    ?>

    <form action="orderpicking.php" method="post">
        <?php
        // List parts
        $sql = "SELECT parts.id, parts.name, orderpart.count, orderpart.packed, 
        (SELECT SUM(count) FROM stock WHERE stock.partId=parts.id) as stock,
        (SELECT SUM(count) FROM extstock WHERE extstock.part=parts.id AND extstock.relation=" . $row["relation"] . ") as extstock
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.count > 0 AND parts.deleted=0 AND orderpart.orderId=$id";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            ?>

            <div>
                <h3>Componenten</h3>
                <table class="styled-table">
                    <thead>
                    <tr>
                        <th>Component</th>
                        <th>Aantal in te pakken</th>
                        <th>Lokatie</th>
                        <th>Voorraad</th>
                        <th>Aantal ingepakt</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $evenrow = 0;
                    while ($prow = $result->fetch_assoc()) {
                        $evenrow = -$evenrow + 1;
                        $pid = $prow["id"];
                        $short = max($prow["count"] - ($prow["stock"] + $prow["extstock"] + $prow["packed"]), 0);


                        $sql = "SELECT stock.id, stock.count, stock.sublocation, locations.name as location
                        FROM `stock` 
                        LEFT JOIN locations ON locations.id=stock.location
                        WHERE stock.count>0 AND stock.partId=$pid;";
                        $stockresult = $conn->query($sql);

                        $rowspan = 1;
                        if ($stockresult && $stockresult->num_rows > 0) {
                            $rowspan = $stockresult->num_rows;
                        }
                        echo("<tr class='" . ($evenrow ? "even" : "odd") . "'>\n");
                        echo("<td rowspan='$rowspan'><a href='item.php?id=$pid'>" . $prow["name"] . "</a></td>\n");
                        echo("<td rowspan='$rowspan'>$short</td>\n");

                        if ($stockresult && $stockresult->num_rows > 0) {
                            $addtr = false;
                            while ($stockrow = $stockresult->fetch_assoc()) {
                                if ($addtr) {
                                    echo("<tr class='" . ($evenrow ? "even" : "odd") . "'>\n");
                                }
                                echo("<td>" . $stockrow["location"] . " " . $stockrow["sublocation"] . "</td>\n");
                                echo("<td>" . $stockrow["count"] . "</td>\n");
                                echo("<td><input type='text' name='pack-" . $stockrow["id"] . "'></td>\n");
                                echo("</tr>\n");
                                $addtr = true;
                            }

                        } else {
                            echo("<td><b>Geen voorraad!</b></td><td></td><td><input type='text' name='pack-c$pid'></td></tr>");
                        }

                    }

                    ?>
                    </tbody>
                </table>
            </div>
            <?php
        }

        ?>
        <input type="hidden" name="id" value="<?php echo($id); ?>"/>
        <input name="save" type="submit" value="Opslaan"/>
    </form>
    <br>
    <form action="generatepicklist.php" method="post" target="_blank">
        <input type="hidden" name="id" value="<?php echo($id); ?>"/>
        <input name="next" type="submit" value="Pick lijst printen"/>
    </form>
    <?php
    printFooter();
} else {
    // Invalid id
    header("Location: index.php");
    exit();
}
