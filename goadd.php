<?php
if ((!isset($_POST["q"]) || strlen($_POST["q"]) == 0) && !isset($_POST["mpn"])) {
    header("Location: add.php");
    exit();
}
require('mysqlConn.php');
require('scan2id.php');
require('nexar.php');
require('header.php');

$input = scan2id(validateInput($_POST["q"]));

printHeader("Toevoegen");

// Check item already exists in the database
$sql = "SELECT parts.id FROM parts  WHERE parts.name = \"$input\"";
$result = $conn->query($sql);
// If item already exists in the database
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    ?>
    <form id="itemForm" action="item.php" method="post">
        <input type="hidden" name="id" value="<?php echo($row["id"]); ?>">
    </form>
    <script type="text/javascript">
        document.getElementById('itemForm').submit();
    </script>

    <?php
    printFooter();
    exit();
}

$mpn = $input;
$description = "";
$category = "-";
$value = "";
$valueUnit = "-";
$package = "-";
$partCount = "0";
$location = "";

$nexarData = json_decode(nexarQuery($input), true);

if (array_key_exists("data", $nexarData)) {
    $nexarData = $nexarData["data"];
    if (array_key_exists("supSearch", $nexarData)) {
        $nexarData = $nexarData["supSearch"];
        if (array_key_exists("results", $nexarData)) {
            $nexarData = $nexarData["results"];
            if (count($nexarData) > 0) {
                $nexarData = $nexarData[0];
                if (array_key_exists("part", $nexarData)) {
                    $nexarData = $nexarData["part"];
                    if (array_key_exists("mpn", $nexarData)) {
                        $mpn = $nexarData["mpn"];
                    }
                    if (array_key_exists("shortDescription", $nexarData)) {
                        $description = $nexarData["shortDescription"];
                    }
                    if (array_key_exists("category", $nexarData)) {
                        $catArray = $nexarData["category"];
                        if (array_key_exists("name", $catArray)) {
                            $category = $catArray["name"];
                        }
                    }

                    // Continue with specs
                    $valueSet = false;
                    $packageSet = false;
                    if (array_key_exists("specs", $nexarData)) {
                        $specs = $nexarData["specs"];
                        for ($i = 0; $i < count($specs); $i++) {
                            $spec = $specs[$i];
                            if (!$valueSet) {
                                if ($spec["valueType"] == "number") {
                                    $value = $spec["value"];
                                    $valueUnit = $spec["units"];
                                    if ($packageSet)
                                        break;
                                    $valueSet = true;
                                }
                            }
                            if (!$packageSet) {
                                if ($spec["attribute"]["name"] == "Case/Package") {
                                    $package = $spec["value"];
                                    if ($valueSet)
                                        break;
                                    $packageSet = true;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// Converts string to database id
function stringToId($table, $value)
{
    global $conn;
    if (strlen($value) == 0) {
        $value = "-";
    }

    $stresult = $conn->query("SELECT DISTINCT $table.id FROM $table WHERE name=\"$value\" LIMIT 1");
    if ($stresult) {
        if ($stresult->num_rows > 0) {
            if ($strow = $stresult->fetch_assoc()) {
                return $strow["id"];
            }
        } else {
            $conn->query("INSERT INTO $table (name) VALUES('$value');");
            $stresult = $conn->query("SELECT DISTINCT $table.id FROM $table WHERE name=\"$value\" LIMIT 1");
            if ($stresult && $stresult->num_rows > 0) {
                if ($strow = $stresult->fetch_assoc()) {
                    return $strow["id"];
                }
            }
        }
    }
    return 1;
}

$category = stringToId("types", $category);
$valueUnit = stringToId("units", $valueUnit);
$package = stringToId("packages", $package);
$location = stringToId("locations", $location);

require('formFunctions.php');
if ($input != $mpn){
    echo("<h2 style='color: #ff0000'>\"$input\" niet gevonden. bedoelde je: \"$mpn\"?</h2>");
}
?>

    <form action="add.php" method="post">
        <table>
            <tr>
                <td>Part:</td>
                <td><input type="text" name="mpn" value="<?php echo("$mpn"); ?>"/></td>
            </tr>
            <tr>
                <td>Omschrijving:</td>
                <td><input type="text" name="desc" value="<?php echo("$description"); ?>"/></td>
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
                    printSublocaton("1"); ?>
                </td>
            </tr>
            <tr>
                <td>Aantal:</td>
                <td><input type="text" name="stock" value="<?php echo("$partCount"); ?>" /></td>
            </tr>
        </table>

        <h4>Tag</h4>
        <input type="text" name="tag" value="" />

        <h4>Gebruikt in</h4>
        <table>
            <tr>
                <th>Project</th>
                <th>Aantal</th>
            </tr>
            <?php
            $sql = "SELECT id, name FROM projects ORDER BY name ASC;";
            $prresult = $conn->query($sql);

            $count = 0;
            if ($prresult && $prresult->num_rows > 0) {
                while ($prrow = $prresult->fetch_assoc()) {
                    echo("<tr>\n");
                    echo("<td>" . $prrow["name"] . "</td>\n");
                    echo("<td>\n");
                    echo("<input type=\"hidden\" name=\"projectid-$count\" value=\"" . $prrow["id"] . "\" />\n");
                    echo("<input type=\"text\" name=\"project-$count\" value=\"0\" />\n");
                    echo("</td>\n");
                    echo("</tr>\n");
                    $count++;
                }
            }
            echo("<input type=\"hidden\" name=\"projectcount\" value=\"$count\" />\n");
            ?>
        </table>
        <input name="submit" type="submit" value="Opslaan"/>
    </form>
    <br/>
<?php
printFooter();