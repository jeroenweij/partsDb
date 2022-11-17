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
    $stock = validateNumberInput($_POST["stock"]);
    $package = validateNumberInput($_POST["select-packages"]);
    $unit = validateNumberInput($_POST["select-units"]);
    $location = validateNumberInput($_POST["select-locations"]);
    $sublocation = validateNumberInput($_POST["sublocation"]);

    if (strlen($partNum) > 0 && strlen($description)>0) {
        $sql = "UPDATE parts SET 
                 name='$partNum', description='$description', type='$type', value='$value', unit='$unit', 
                 package='$package', stock='$stock', location='$location', sublocation='$sublocation' 
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
$partCount = $part["stock"];
$location = $part["location"];
$sublocation = $part["sublocation"];

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
                <td>Lokatie:</td>
                <td>
                    <?php printSelect("locations", $location);
                    printSublocaton($sublocation); ?>
                </td>
            </tr>
            <tr>
                <td>Aantal:</td>
                <td><input type="text" name="stock" value="<?php echo("$partCount"); ?>"/></td>
            </tr>
        </table>

        <input name="submit" type="submit" value="Opslaan"/>
    </form>
    <br/>
<?php
printFooter();