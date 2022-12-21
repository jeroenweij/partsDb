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
    $newcount= max($oldcount - $count,0);
    $conn->query("UPDATE orderpart SET count=$newcount WHERE orderId = $id AND part = $part;");
}

if (isset($_POST["select-projects"])) {
    $project = validateNumberInput($_POST["select-projects"]);
    $count = validateNumberInput($_POST["count"]);
    if (strlen($count) == 0) {
        $count = 1;
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
        $sql = "SELECT parts.id, partproject.count FROM parts LEFT JOIN partproject ON partproject.part=parts.id WHERE partproject.project=$project;";
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
            WHERE orderproject.orderId=$id";
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
    // List parts
    $sql = "SELECT parts.id, parts.name, orderpart.count, orderpart.packed, 
        (SELECT SUM(count) FROM stock WHERE stock.partId=parts.id) as stock,
        (SELECT SUM(count) FROM extstock WHERE extstock.part=parts.id AND extstock.relation=".$row["relationid"].") as extstock
            FROM parts 
            LEFT JOIN orderpart ON orderpart.part=parts.id 
            WHERE orderpart.orderId=$id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        ?>

        <div>
            <h3>Componenten</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>Component</th>
                    <th>Aantal</th>
                    <th>Externe voorraad</th>
                    <th>Voorraad</th>
                    <th>Ingepakt</th>
                    <th>Te kort</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($prow = $result->fetch_assoc()) {
                    $pid = $prow["id"];
                    $short = max($prow["count"] - ($prow["stock"] + $prow["extstock"] + $prow["packed"]),0);
                    echo("<tr>\n");
                    echo("<td><a href='item.php?id=$pid'>" . $prow["name"] . "</a></td>\n");
                    echo("<td>" . $prow["count"] . "</td>\n");
                    echo("<td>" . $prow["extstock"] . "</td>\n");
                    echo("<td>" . $prow["stock"] . "</td>\n");
                    echo("<td>" . $prow["packed"] . "</td>\n");
                    echo("<td>$short</td>\n");
                    echo("</tr>\n");
                }

                ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    if ($row["statusid"] == 1) { ?>
        <form action="order.php" method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>" />
            <input type="hidden" name="newstatus" value="2" />
            <input name="next" type="submit" value="Naar componenten verzamelen" onclick="return confirm('Weet je het zeker?\nje kan niet meer terug')" />
        </form>
    <?php
    }
    printFooter();
} else {
    // Invalid id
    header("Location: index.php");
    exit();
}
