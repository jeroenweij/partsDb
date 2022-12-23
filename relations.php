<?php
$fileList = get_included_files();
$topfile = basename($fileList[0]);

require('mysqlConn.php');
require('header.php');

if (isset($_POST["new"]) && strlen($_POST["name"]) > 0) {
    $newName = $_POST["name"];
    $contact = $_POST["contact"];
    $address = $_POST["address"];
    $sql = "INSERT INTO relations (name, contact, address) SELECT $newName, $contact, $address
        FROM DUAL WHERE NOT EXISTS (
        SELECT name FROM relations WHERE name = $newName AND contact = $contact);";
    $conn->query($sql);
}

printHeader("Relaties");

if (isset($_POST["edit-id"])) {
    if (isset($_POST["newname"])) {
        if (strlen($_POST["newname"]) > 0) {
            $sql = "UPDATE relations SET 
                     name='" . $_POST["newname"] . "', 
                     contact='" . $_POST["newcontact"] . "', 
                     address='" . $_POST["newaddress"] . "' 
                     WHERE id=" . $_POST["edit-id"];
            $conn->query($sql);
        }
    } else {
        echo("<h3>" . $_POST["oldname"] . " aanpassen:</h3>");
        ?>
        <form action="relations.php" method="post">
            <input type="hidden" name="edit-id" value="<?php echo($_POST["edit-id"]); ?>"/>
            <table>
                <tr>
                    <td>Naam</td>
                    <td><input type="text" name="newname" value="<?php echo($_POST["oldname"]); ?>"/></td>
                </tr>
                <tr>
                    <td>Contact persoon</td>
                    <td><input type="text" name="newcontact" value="<?php echo($_POST["oldcontact"]); ?>"/></td>
                </tr>
                <tr>
                    <td>Adres</td>
                    <td><textarea name="newaddress" rows="5" cols="80"><?php echo($_POST["oldaddress"]); ?></textarea></td>
                </tr>
            </table>
            <input name="save" type="submit" value="Opslaan"/>
        </form>
        <?php
    }
}

$sql = "SELECT id, name, contact, address, (SELECT COUNT(*) FROM orders WHERE orders.relation = relations.id) AS ref FROM relations;";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    ?>
    <table class="styled-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Contact</th>
            <th>Adres</th>
            <th>aantal keer gebruikt</th>
            <th>componenten</th>
            <th>aanpassen</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = $result->fetch_assoc()) {
            echo("    <tr>\n");
            echo("        <td>" . $row["id"] . "</td>\n");
            echo("        <td>" . $row["name"] . "</td>\n");
            echo("        <td>" . $row["contact"] . "</td>\n");
            echo("        <td>" . $row["address"] . "</td>\n");
            echo("        <td>" . $row["ref"] . "</td>\n");

            echo("<td><form action=\"orders.php\" method=\"post\">\n");
            echo("<input type=\"hidden\" name=\"view-relations\" value=\"" . $row["id"] . "\">\n");
            echo("<input type=\"submit\" value=\"Bekijken\" />\n");
            echo("</form></td>\n");

            echo("<td><form action=\"$topfile\" method=\"post\">\n");
            echo("<input type=\"hidden\" name=\"edit-id\" value=\"" . $row["id"] . "\">\n");
            echo("<input type=\"hidden\" name=\"oldname\" value=\"" . $row["name"] . "\">\n");
            echo("<input type=\"hidden\" name=\"oldcontact\" value=\"" . $row["contact"] . "\">\n");
            echo("<input type=\"hidden\" name=\"oldaddress\" value=\"" . $row["address"] . "\">\n");
            echo("<input name=\"delete\" type=\"submit\" value=\"Aanpassen\" />\n");
            echo("</form></td>\n");

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
    <form action="relations.php" method="post">
        <input name="new" type="hidden" value="add"/> &nbsp;
        <table>
            <tr>
                <td>Naam</td>
                <td><input type="text" name="name" value=""/></td>
            </tr>
            <tr>
                <td>Contact persoon</td>
                <td><input type="text" name="contact" value=""/></td>
            </tr>
            <tr>
                <td>Adres</td>
                <td><textarea name="address" rows="5" cols="80"></textarea></td>
            </tr>
        </table>
        <input name="save" type="submit" value="Opslaan"/>
    </form>

<?php
printFooter();
