<?php
if (!isset($table) || !isset($title)) {
    header("Location: index.php");
    exit();
}
$fileList = get_included_files();
$topfile = basename($fileList[0]);

require('mysqlConn.php');
require('header.php');

if (isset($_POST["del-id"])) {
    $sql = "DELETE FROM $table WHERE id=" . $_POST["del-id"];
    $conn->query($sql);
}

if (isset($_POST["new"]) && strlen($_POST["new"]) > 0) {
    $newName = $_POST["new"];
    $sql = "INSERT INTO $table (name)
SELECT * FROM (SELECT '$newName' AS name) AS temp
WHERE NOT EXISTS (
    SELECT name FROM $table WHERE name = '$newName'
) LIMIT 1;";
    $conn->query($sql);
}

printHeader($title);

if (isset($_POST["edit-id"])) {
    if (isset($_POST["newname"])) {
        if (strlen($_POST["newname"]) > 0) {
            $sql = "UPDATE $table SET name='".$_POST["newname"]."' WHERE id=" . $_POST["edit-id"];
            $conn->query($sql);
        }
    } else {
        echo("<h3>" . $_POST["oldname"] . " aanpassen:</h3>");
        ?>
        <form action="<?php echo($topfile); ?>" method="post">
            <input type="hidden" name="edit-id" value="<?php echo($_POST["edit-id"]); ?>"/>
            <input type="text" name="newname" value="<?php echo($_POST["oldname"]); ?>"/>
            <input name="save" type="submit" value="Opslaan"/>
        </form>
        <?php
    }
}

$refdb = "parts";
$refname = substr($table, 0, -1);
if ($table == "projects") {
    $refdb = "partproject";
}
if ($table == "locations") {
    $refdb = "stock";
}
$sql = "SELECT id, name, (SELECT COUNT(*) FROM $refdb WHERE $refdb.$refname = $table.id) AS ref FROM $table;";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    ?>
    <table class="styled-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>naam</th>
            <th>aantal keer gebruikt</th>
            <th>componenten</th>
            <th>aanpassen</th>
            <th>verwijderen</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = $result->fetch_assoc()) {
            echo("    <tr>\n");
            echo("        <td>" . $row["id"] . "</td>\n");
            echo("        <td>" . $row["name"] . "</td>\n");
            echo("        <td>" . $row["ref"] . "</td>\n");

            echo("<td><form action=\"list.php\" method=\"post\">\n");
            echo("<input type=\"hidden\" name=\"view-$table\" value=\"" . $row["id"] . "\">\n");
            echo("<input type=\"submit\" value=\"Bekijken\" />\n");
            echo("</form></td>\n");

            if ($row["id"] > 1 || $table == "projects") {
                echo("<td><form action=\"$topfile\" method=\"post\">\n");
                echo("<input type=\"hidden\" name=\"edit-id\" value=\"" . $row["id"] . "\">\n");
                echo("<input type=\"hidden\" name=\"oldname\" value=\"" . $row["name"] . "\">\n");
                echo("<input name=\"delete\" type=\"submit\" value=\"Aanpassen\" />\n");
                echo("</form></td>\n");
            } else {
                echo("        <td></td>\n");
            }

            if ($row["ref"] == 0 && ($row["id"] > 1 || $table == "projects")) {
                echo("<td><form action=\"$topfile\" method=\"post\">\n");
                echo("<input type=\"hidden\" name=\"del-id\" value=\"" . $row["id"] . "\">\n");
                echo("<input name=\"delete\" type=\"submit\" value=\"Verwijderen\"  onclick=\"return confirm('Weet je het zeker?')\" />\n");
                echo("</form></td>\n");
            } else {
                echo("        <td></td>\n");
            }
            echo("    </tr>\n");
        }
        ?>
        </tbody>
    </table>

    <br/>
    <?php
}
?>
    <h3>Toevoegen</h3>
    <form action="<?php echo($table); ?>.php" method="post">
        <input name="new" type="text" value=""/> &nbsp;
        <input class="twohndrdpx" name="submit" type="submit" value="Toevoegen"/>
    </form>

<?php
printFooter();
