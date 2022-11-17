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

if (isset($_POST["diff"])) {
    $diff = validateNumberInput($_POST["diff"]);
    $operator = validateInput($_POST["adjust"]);

    if ($operator == "=") {
        $sql = "UPDATE parts SET stock=$diff WHERE id=$id";
    } else {
        if (strlen($diff) == 0) {
            $diff = 1;
        }
        $sql = "UPDATE parts SET stock=stock $operator $diff WHERE id=$id";
    }
    if (strlen($diff) > 0) {
        $conn->query($sql);
    }
}
if (isset($_POST["select-locations"])) {
    $location = validateNumberInput($_POST["select-locations"]);
    $sublocation = validateNumberInput($_POST["sublocation"]);
    $sql = "UPDATE parts SET location=$location, sublocation=$sublocation WHERE id=$id";
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


$sql = "SELECT parts.id, parts.name, parts.description, parts.stock, parts.value, parts.sublocation,
       types.name as type, 
       units.name as unit,
       packages.name as package,
       locations.name as location,
       locations.id as locid
        FROM parts 
        LEFT JOIN types ON parts.type=types.id
        LEFT JOIN units ON parts.unit=units.id
        LEFT JOIN packages ON parts.package=packages.id
        LEFT JOIN locations ON parts.location=locations.id
        WHERE parts.id='$id'";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    printHeader($row["name"]);

    echo("<p>" . $row["description"] . "</p>");
    ?>
    <div style="height: 200px">
        <div style="float: right">
            <table>
                <tr>
                    <th class="centercell twohndrdpx"><h3>Locatie</h3></th>
                    <th class="centercell twohndrdpx"><h3>Vooraad</h3></th>
                </tr>
                <tr>
                    <td class="centercell twohndrdpx">
                        <b><?php echo($row["location"] . " " . $row["sublocation"]); ?></b></td>
                    <td class="centercell twohndrdpx"><b><?php echo($row["stock"]); ?></b></td>
                </tr>
            </table>
        </div>
        <div style="float: left">
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
    </div>
    <div>
        <br/>
        <h3>Vooraad aanpassen</h3>
        <form action="item.php" method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>">
            <input style="width: 50px" name="adjust" type="submit" value="-"/>
            <input name="diff" type="text" value=""/>
            <input style="width: 50px" name="adjust" type="submit" value="="/>
            <input style="width: 50px" name="adjust" type="submit" value="+"/>
        </form>
    </div>
    <div>
        <h3>Lokatie aanpassen</h3>
        <form action="item.php" method="post">
            <input type="hidden" name="id" value="<?php echo($id); ?>">
            <?php printSelect("locations", $row["locid"]);
            printSublocaton($row["sublocation"]); ?>
            <input name="opslaan" type="submit" value="Opslaan"/>
        </form>
    </div>
    <?php
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
            <input name="opslaan" type="submit" value="Edit"/>
        </form>
    </div>
    <?php
    printFooter();
} else {
    // Invalid id
    header("Location: index.php");
    exit();
}
