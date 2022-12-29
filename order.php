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

if (isset($_POST["newstatus"])) {
    $newstatus = $_POST["newstatus"];
    $conn->query("UPDATE orders SET orders.status=$newstatus WHERE orders.id=$id;");
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

function removePart($part, $count)
{
    global $id;
    global $conn;

    $oldcount = 0;
    $result = $conn->query("SELECT count FROM orderpart WHERE orderId = $id AND part = $part;");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $oldcount = $row["count"];
    }
    $newcount = max($oldcount - $count, 0);
    $conn->query("UPDATE orderpart SET count=$newcount WHERE orderId = $id AND part = $part;");
}

if (isset($_POST["select-projects"])) {
    $project = validateNumberInput($_POST["select-projects"]);
    $count = validateNumberInput($_POST["count"]);
    if (strlen($count) == 0) {
        $count = 0;
    }
    $oldcount = 0;
    $result = $conn->query("SELECT count FROM orderproject WHERE orderId = $id AND project = $project;");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $oldcount = $row["count"];
    }

    if ($count > $oldcount) {
        $conn->query("INSERT INTO orderproject (orderId, project) SELECT $id, $project  
        FROM DUAL WHERE NOT EXISTS (
        SELECT count FROM orderproject WHERE orderId = $id AND project = $project);");
        $conn->query("UPDATE orderproject SET count = $count WHERE orderId = $id AND project = $project;");

        $diff = $count - $oldcount;

        // add $diff * parts
        $sql = "SELECT parts.id, partproject.count FROM parts LEFT JOIN partproject ON partproject.part=parts.id WHERE deleted=0 AND partproject.project=$project;";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                addPart($row["id"], $row["count"] * $diff);
            }
        }
    } else if ($count < $oldcount) {
        $conn->query("UPDATE orderproject SET count = $count WHERE orderId = $id AND project = $project;");
        $diff = $oldcount - $count;

        // remove $diff * parts
        $sql = "SELECT parts.id, partproject.count FROM parts LEFT JOIN partproject ON partproject.part=parts.id WHERE partproject.project=$project;";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                removePart($row["id"], $row["count"] * $diff);
            }
        }
    }
}

$sql = "SELECT orders.id, orders.name,
       companys.name as company,
       companys.id as companyid,
       relations.name as relation,
       relations.id as relationid,
       statuses.name as status,
       statuses.id as statusid
        FROM orders 
        LEFT JOIN relations ON orders.relation=relations.id
        LEFT JOIN companys ON orders.company=companys.id
        LEFT JOIN statuses ON orders.status=statuses.id 
        WHERE orders.id='$id'";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    printHeader($row["name"]);

    ?>
    <table>
        <tr>
            <td>Status:</td>
            <td><?php echo($row["status"]); ?></td>
        </tr>
        <tr>
            <td>Van:</td>
            <td><?php echo($row["company"]); ?></td>
        </tr>
        <tr>
            <td>Naar:</td>
            <td><?php echo($row["relation"]); ?></td>
        </tr>

    </table>

    <?php
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
    <?php if ($row["statusid"] == 1) { ?>
        <div>
            <h3>Project aantal aanpassen</h3>
            <form action="order.php" method="post">
                <input type="hidden" name="id" value="<?php echo($id); ?>">
                <table>
                    <tr>
                        <td>Project:</td>
                        <td><?php printSelect("projects", 0); ?></td>
                    </tr>
                    <tr>
                        <td>Aantal:</td>
                        <td><input style="width: 100px" name="count" type="text" value="1"/></td>
                    </tr>
                </table>

                <input name="opslaan" type="submit" value="Opslaan"/>
            </form>
        </div>
    <?php } ?>


    <?php

    function echoif($status, $text)
    {
        global $row;
        if (in_array($row["statusid"], $status)) {
            echo($text);
        }
    }

    // List parts
    $sql = "SELECT parts.id, parts.name, orderpart.count, orderpart.packed, 
        (SELECT SUM(count) FROM stock WHERE stock.partId=parts.id) as stock,
        (SELECT SUM(count) FROM extstock WHERE extstock.part=parts.id AND extstock.relation=" . $row["relationid"] . ") as extstock
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.count > 0 AND parts.deleted=0 AND orderpart.orderId=$id";
    $result = $conn->query($sql);
    $componentcount = 0;
    if ($result && $result->num_rows > 0) {
        ?>

        <div>
            <h3>Componenten</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <?php
                    echo("<th>Component</th>");
                    echo("<th>Aantal nodig</th>");
                    echoif(array(1, 2), "<th>Externe voorraad</th>");
                    echo("<th>Voorraad</th>");
                    echoif(array(1, 2), "<th>Te kort</th>");
                    echoif(array(2), "<th>Ingepakt</th>");
                    echoif(array(3, 4), "<th>Verzonden</th>");
                    echoif(array(2), "<th>Nog inpakken</th>");
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($prow = $result->fetch_assoc()) {
                    $componentcount++;
                    $pid = $prow["id"];
                    $short = max($prow["count"] - ($prow["stock"] + $prow["extstock"] + $prow["packed"]), 0);
                    $shortpacked = max($prow["count"] - ($prow["packed"] + $prow["extstock"]), 0);
                    echo("<tr>\n");
                    echo("<td><a href='item.php?id=$pid'>" . $prow["name"] . "</a></td>\n");
                    echo("<td>" . $prow["count"] . "</td>\n");
                    echoif(array(1, 2), "<td>" . $prow["extstock"] . "</td>\n");
                    echo("<td>" . $prow["stock"] . "</td>\n");
                    echoif(array(1, 2), "<td>$short</td>\n");
                    echoif(array(2, 3, 4),"<td>" . $prow["packed"] . "</td>\n");
                    echoif(array(2),"<td>$shortpacked</td>\n");
                    echo("</tr>\n");
                }

                ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    if ($row["statusid"] == 1 && $componentcount > 0) { ?>
        <br>
        <div>
            <form action="order.php" method="post">
                <input type="hidden" name="id" value="<?php echo($id); ?>"/>
                <input type="hidden" name="newstatus" value="2"/>
                <input name="next" type="submit" value="Naar componenten verzamelen"
                       onclick="return confirm('Weet je het zeker?\nje kan niet meer terug')"/>
            </form>
        </div>
        <?php
    } else if ($row["statusid"] == 2) { ?>
        <br>
        <div>
            <table>
                <tr>
                    <td>
                        <form action="orderpicking.php" method="post">
                            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
                            <input name="next" type="submit" value="Inpakken"/>
                        </form>
                    </td>
                    <td>
                        <form action="generatepicklist.php" method="post" target="_blank">
                            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
                            <input name="next" type="submit" value="Pick lijst printen"/>
                        </form>
                    </td>
                    <td>
                        <form action="generatepackinglist.php" method="post" target="_blank">
                            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
                            <input name="next" type="submit" value="Pakbon printen"/>
                        </form>
                    </td>
                    <td>
                        <form action="order.php" method="post">
                            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
                            <input type="hidden" name="newstatus" value="3"/>
                            <input name="next" type="submit" value="Verzenden"
                                   onclick="return confirm('Weet je het zeker?\nIs alles ingepakt?\nJe kan niet meer terug!')"/>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    } else if ($row["statusid"] == 3) { ?>
        <br>
        <div>
            <table>
                <tr>
                    <td>
                        <form action="generatepackinglist.php" method="post" target="_blank">
                            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
                            <input name="next" type="submit" value="Pakbon printen"/>
                        </form>
                    </td>
                    <td>
                        <form action="orderprocessing.php" method="post">
                            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
                            <input type="hidden" name="newstatus" value="3"/>
                            <input name="next" type="submit" value="Voorraad verwerken"/>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    if ($row["statusid"] < 3) {
        ?>
        <div>
            <h3>Order aanpassen</h3>
            <form action="neworder.php" method="post">
                <input type="hidden" name="edit-id" value="<?php echo($id); ?>">
                <input type="hidden" name="oldname" value="<?php echo($row["name"]); ?>">
                <input type="hidden" name="oldcompany" value="<?php echo($row["companyid"]); ?>">
                <input type="hidden" name="oldrelation" value="<?php echo($row["relationid"]); ?>">
                <input name="opslaan" type="submit" value="Aanpassen"/>
            </form>
        </div>
        <?php
    }
    printFooter();
} else {
    // Invalid id
    header("Location: index.php");
    exit();
}
