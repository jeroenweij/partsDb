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

if (isset($_POST["del-id"])) {
    $sql = "UPDATE stock SET deleted=1 WHERE id=" . $_POST["del-id"];
    $conn->query($sql);
}

if (isset($_POST["diff"])) {
    $diff = validateNumberInput($_POST["diff"]);
    $operator = validateInput($_POST["adjust"]);
    $stockid = validateNumberInput($_POST["stockid"]);
    $table = validateInput($_POST["table"]);

    if ($operator == "=") {
        $sql = "UPDATE $table SET count=$diff WHERE id=$stockid";
    } else {
        if (strlen($diff) == 0) {
            $diff = 1;
        }
        $sql = "UPDATE $table SET count=GREATEST(count $operator $diff, 0) WHERE id=$stockid";
    }
    if (strlen($diff) > 0) {
        $conn->query($sql);
    }
}
if (isset($_POST["select-locations"])) {
    $location = validateNumberInput($_POST["select-locations"]);
    $subloc = validateNumberInput($_POST["sublocation"]);
    $count = validateNumberInput($_POST["count"]);
    if ($location > 1) {
        $conn->query("INSERT INTO stock (partId, location, sublocation, count) SELECT $id, $location, $subloc, 0
        FROM DUAL WHERE NOT EXISTS (
        SELECT count FROM stock WHERE partId = $id AND location = $location AND sublocation = $subloc);");

        $sql = "UPDATE stock SET count=count + $count, deleted=0 WHERE partId = $id AND location = $location AND sublocation = $subloc";
        $conn->query($sql);
    }
}

if (isset($_POST["select-relations"])) {
    $relation = validateNumberInput($_POST["select-relations"]);
    $count = validateNumberInput($_POST["count"]);

    $conn->query("INSERT INTO extstock (part, relation, count) SELECT $id, $relation, 0
        FROM DUAL WHERE NOT EXISTS (
        SELECT count FROM extstock WHERE part = $id AND relation = $relation);");

    $sql = "UPDATE extstock SET count=count + $count WHERE part=$id AND relation=$relation";
    $conn->query($sql);
}

if (isset($_POST["del-project"])) {
    $project = validateNumberInput($_POST["del-project"]);
    $conn->query("DELETE FROM partproject WHERE part = $id AND project = $project;");
}
if (isset($_POST["del-tag"])) {
    $tag = validateInput($_POST["del-tag"]);
    $conn->query("DELETE FROM tags WHERE tag ='$tag';");
}
if (isset($_POST["new-tag"])) {
    $tag = validateInput($_POST["new-tag"]);
    $conn->query("INSERT INTO `tags` (`tag`, `part`) VALUES ('$tag', '$id');");
}
if (isset($_POST["select-projects"])) {
    $project = validateNumberInput($_POST["select-projects"]);
    $count = validateNumberInput($_POST["count"]);
    if (strlen($count) == 0) {
        $count = 1;
    }
    $conn->query("INSERT INTO partproject (part, project) SELECT $id, $project  
        FROM DUAL WHERE NOT EXISTS (
        SELECT count FROM partproject WHERE part = $id AND project = $project);");
    $conn->query("UPDATE partproject SET count = $count WHERE part = $id AND project = $project;");
}


$sql = "SELECT parts.id, parts.name, parts.description, parts.value,
       types.name as type, 
       units.name as unit,
       packages.name as package
        FROM parts 
        LEFT JOIN types ON parts.type=types.id
        LEFT JOIN units ON parts.unit=units.id
        LEFT JOIN packages ON parts.package=packages.id
        WHERE parts.id='$id'";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    printHeader($row["name"]);

    echo("<p>" . $row["description"] . "</p>");
    ?>
    <div style="height: 200px">
        <table>
            <tr>
                <td>categorie:</td>
                <td><?php echo($row["type"]); ?></td>
            </tr>
            <tr>
                <td>waarde:</td>
                <td><?php echo($row["value"] . " " . $row["unit"]); ?></td>
            </tr>
            <tr>
                <td>package:</td>
                <td><?php echo($row["package"]); ?></td>
            </tr>
        </table>
    </div>

    <?php

    // Stock table
    $stockresult = $conn->query("SELECT stock.id, stock.sublocation, stock.count, locations.name as location FROM stock LEFT JOIN locations ON stock.location=locations.id WHERE (stock.deleted=0 OR stock.count>0) AND stock.partId = " . $row["id"]);
    if ($stockresult && $stockresult->num_rows > 0) {
        ?>
        <div>
            <h3>Voorraad</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>Locatie</th>
                    <th>Voorraad</th>
                    <th>Aanpassen</th>
                    <th>Verbergen</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($stockrow = $stockresult->fetch_assoc()) {
                    echo("<tr>\n");
                    echo("    <td><b>" . $stockrow["location"] . " " . $stockrow["sublocation"] . "</b></td>\n");
                    echo("    <td><b>" . $stockrow["count"] . "</b></td>\n");
                    ?>
                    <td>
                        <form action="item.php" method="post">
                            <input type="hidden" name="id" value="<?php echo($id); ?>">
                            <input type="hidden" name="table" value="stock">
                            <input type="hidden" name="stockid" value="<?php echo($stockrow["id"]); ?>">
                            <input style="width: 50px" name="adjust" type="submit" value="-"/>
                            <input style="width: 50px" name="diff" type="text" value=""/>
                            <input style="width: 50px" name="adjust" type="submit" value="="/>
                            <input style="width: 50px" name="adjust" type="submit" value="+"/>
                        </form>
                    </td>
                    <td>
                        <?php if ($stockrow["count"] == 0) { ?>
                            <form method="post">
                                <input type="hidden" name="del-id" value="<?php echo($stockrow["id"]); ?>"/>
                                <input name="delete" type="submit" value="Verbergen"
                                       onclick="return confirm('Weet je het zeker?')"/>
                            </form>
                        <?php } ?>
                    </td>
                    <?php
                    echo("</tr>\n");
                }

                ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    ?>
    <div>
        <h3>Voorraad toevoegen</h3>
        <form action="item.php" method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>">
            <?php printSelect("locations", 0);
            printSublocaton(0); ?>
            <input style="width: 100px" name="count" type="text" value="1"/>
            <input name="opslaan" type="submit" value="Opslaan"/>
        </form>
    </div>
    <?php

    // Packed stock table
    $stockresult = $conn->query("SELECT orderpart.part, orderpart.packed, orders.name, relations.name as relation 
                                        FROM orderpart 
                                        LEFT JOIN orders ON orderpart.orderId=orders.id 
                                        LEFT JOIN relations ON relations.id=orders.relation 
                                        WHERE orders.status < 4
                                        AND orderpart.packed > 0 AND orderpart.part=" . $row["id"]);
    if ($stockresult && $stockresult->num_rows > 0) {
        ?>
        <div>
            <h3>Voorraad gereserveerd voor assemblage</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>Order</th>
                    <th>Assembleur</th>
                    <th>Aantal</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($stockrow = $stockresult->fetch_assoc()) {
                    echo("<tr>\n");
                    echo("    <td><b>" . $stockrow["name"] . "</b></td>\n");
                    echo("    <td><b>" . $stockrow["relation"] . "</b></td>\n");
                    echo("    <td><b>" . $stockrow["packed"] . "</b></td>\n");
                    echo("</tr>\n");
                }

                ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // External stock table
    $stockresult = $conn->query("SELECT extstock.id, extstock.count, relations.name as location FROM extstock LEFT JOIN relations ON extstock.relation=relations.id WHERE extstock.count > 0 AND extstock.part = " . $row["id"]);
    if ($stockresult && $stockresult->num_rows > 0) {
        ?>
        <div>
            <h3>Externe voorraad</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>Locatie</th>
                    <th>Voorraad</th>
                    <th>Aanpassen</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($stockrow = $stockresult->fetch_assoc()) {
                    echo("<tr>\n");
                    echo("    <td><b>" . $stockrow["location"] . "</b></td>\n");
                    echo("    <td><b>" . $stockrow["count"] . "</b></td>\n");
                    ?>
                    <td>
                        <form action="item.php" method="post">
                            <input type="hidden" name="id" value="<?php echo($id); ?>">
                            <input type="hidden" name="table" value="extstock">
                            <input type="hidden" name="stockid" value="<?php echo($stockrow["id"]); ?>">
                            <input style="width: 50px" name="adjust" type="submit" value="-"/>
                            <input style="width: 50px" name="diff" type="text" value=""/>
                            <input style="width: 50px" name="adjust" type="submit" value="="/>
                            <input style="width: 50px" name="adjust" type="submit" value="+"/>
                        </form>
                    </td>
                    <?php
                    echo("</tr>\n");
                }

                ?>
                </tbody>
            </table>
        </div>
        <?php
    }


    $sql = "SELECT partproject.count, projects.name, projects.id FROM partproject LEFT JOIN projects ON partproject.project = projects.id WHERE partproject.part=$id";
    $prresult = $conn->query($sql);

    if ($prresult && $prresult->num_rows > 0) {
        ?>

        <div>
            <h3>Projecten</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>Project</th>
                    <th>Aantal</th>
                    <th>verwijderen</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($prrow = $prresult->fetch_assoc()) {
                    echo("<tr>\n");
                    echo("<td>" . $prrow["name"] . "</td>\n");
                    echo("<td>" . $prrow["count"] . "</td>\n");
                    echo("<td><form action=\"item.php\" method=\"post\">\n");
                    echo("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
                    echo("<input type=\"hidden\" name=\"del-project\" value=\"" . $prrow["id"] . "\">\n");
                    echo("<input name=\"delete\" type=\"submit\" value=\"Delete\"  onclick=\"return confirm('Weet je het zeker?')\" />\n");
                    echo("</form></td>\n");
                    echo("</tr>\n");
                }

                ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <div>
        <h3>Link project</h3>

        <form action="item.php" method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>">
            <?php printSelect("projects", ""); ?>
            <input name="count" type="text" value=""/>
            <input name="opslaan" type="submit" value="Link"/>
        </form>
    </div>
    <?php
    $sql = "SELECT tag FROM tags WHERE part=$id";
    $tresult = $conn->query($sql);

    if ($tresult && $tresult->num_rows > 0) {
        ?>
        <div>
            <h3>Tags</h3>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>Tag</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($prrow = $tresult->fetch_assoc()) {
                    echo("<tr>\n");
                    echo("<td>" . $prrow["tag"] . "</td>\n");
                    echo("<td><form action=\"item.php\" method=\"post\">\n");
                    echo("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
                    echo("<input type=\"hidden\" name=\"del-tag\" value=\"" . $prrow["tag"] . "\">\n");
                    echo("<input name=\"delete\" type=\"submit\" value=\"Delete\"  onclick=\"return confirm('Weet je het zeker?')\" />\n");
                    echo("</form></td>\n");
                    echo("</tr>\n");
                }
                ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <div>
        <h3>Tag toevoegen</h3>

        <form action="item.php" method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>">
            <input name="new-tag" type="text" value=""/>
            <input name="opslaan" type="submit" value="opslaan"/>
        </form>
    </div>
    <div>
        <h3>Component aanpassen</h3>
        <form action="edit.php" method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>">
            <input name="opslaan" type="submit" value="Aanpassen"/>
        </form>
    </div>
    <?php
    printFooter();
} else {
    // Invalid id
    header("Location: index.php");
    exit();
}
