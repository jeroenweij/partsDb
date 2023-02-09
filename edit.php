<?php
if (!isset($_POST["id"])) {
    header("Location: index.php");
    exit();
}
require('mysqlConn.php');
require('header.php');

// If saving results
if (isset($_POST["mpn"])){
    $partNum = substr(validateInput($_POST["mpn"]), 0, 80);
    $description = substr(validateInput($_POST["desc"]), 0, 120);
    $type = validateNumberInput($_POST["select-types"]);
    $value = validateNumberInput($_POST["value"]);
    $package = validateNumberInput($_POST["select-packages"]);
    $unit = validateNumberInput($_POST["select-units"]);
    $deleted = isset($_POST["deleted"]) ? 1 : 0;

    if (strlen($partNum) > 0) {
        $sql = "UPDATE parts SET 
                 name='$partNum', description='$description', type='$type', value='$value', unit='$unit', 
                 package='$package', deleted='$deleted'
             WHERE parts.id = " . $_POST["id"];
        $conn->query($sql);
    }

    // header("Location: item.php?id=" . $_POST["id"]);
    // exit();
}

// Check item exists in the database
$sql = "SELECT * FROM parts  WHERE parts.id = " . $_POST["id"];
$result = $conn->query($sql);

if (!$result || $result->num_rows < 1) {
    header("Location: index.php");
    exit();
}
$part = $result->fetch_assoc();

printHeader("Edit " . $part["name"]);

$mpn = $part["name"];
$description = $part["description"];
$category = $part["type"];
$value = $part["value"];
$valueUnit = $part["unit"];
$package = $part["package"];
$deleted = $part["deleted"] == 1 ? "checked" : "";

require('formFunctions.php');

?>

    <form action="edit.php" method="post">
        <input type="hidden" name="id" value="<?php echo($part["id"]); ?>" />
        <table>
            <tr>
                <td>Part:</td>
                <td><input type="text" name="mpn" value="<?php echo("$mpn"); ?>"/></td>
            </tr>
            <tr>
                <td>Omschrijving:</td>
                <td><textarea id="desc" name="desc" rows="4" cols="80"><?php echo("$description"); ?></textarea></td>
            </tr>
            <tr>
                <td>Categorie:</td>
                <td><?php printSelect("types", $category); ?></td>
            </tr>
            <tr>
                <td>Waarde:</td>
                <td><input type="text" name="value" value="<?php echo("$value"); ?>"/></td>
            </tr>
            <tr>
                <td>Units:</td>
                <td><?php printSelect("units", $valueUnit); ?></td>
            </tr>
            <tr>
                <td>Package:</td>
                <td><?php printSelect("packages", $package); ?></td>
            </tr>
            <tr>
                <td>Verwijderd:</td>
                <td><input type="checkbox" name="deleted" <?php echo($deleted);?> /></td>
            </tr>
        </table>

        <input name="submit" type="submit" value="Opslaan"/>
    </form>
<br>
    <div>
        <h3>Externe voorraad toevoegen</h3>
        <form action="item.php" method="post">
            <input type="hidden" name="id" value="<?php echo($part["id"]); ?>">
            <?php printSelect("relations", 0); ?>
            <input style="width: 100px" name="count" type="text" value="1"/>
            <input name="opslaan" type="submit" value="Opslaan"/>
        </form>
    </div>
<br>
    <form action="item.php" method="post">
        <input type="hidden" name="id" value="<?php echo($part["id"]); ?>" />
        <input name="submit" type="submit" value="Terug"/>
    </form>
    <br/>
<?php
printFooter();