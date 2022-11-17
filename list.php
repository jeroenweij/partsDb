<?php

require('mysqlConn.php');
require('scan2id.php');
require('header.php');

printHeader("Componenten");

$input = "";
if (isset($_POST["q"])) {
    $input = scan2id(validateInput($_POST["q"]));
}

function printSelect($table)
{
    global $conn;
    $itemname = "view-$table";
    $selectedValue = 0;
    if (isset($_POST[$itemname])) {
        $selectedValue = $_POST[$itemname];
    }

    echo(substr($table, 0, -1) . " ");
    echo "<select name=\"$itemname\">\n";
    echo "<option value=\"0\">All</option>\n";
    $stresult = $conn->query("SELECT DISTINCT name, id FROM $table ORDER BY name");
    if ($stresult && $stresult->num_rows > 0) {
        // output data of each row
        while ($strow = $stresult->fetch_assoc()) {
            $selected = "";
            if ($strow["id"] == $selectedValue) {
                $selected = "selected";
            }
            echo "<option value=\"" . $strow["id"] . "\" $selected>" . $strow["name"] . "</option>\n";
        }
    }
    echo "</select> \n";
}

function printSublocaton($selectedValue)
{
    echo("<select name=\"sublocation\">\n");
    $start = 1;
    $selected = "";
    if (0 == $selectedValue) {
        $selected = "selected";
    }
    echo("<option value=\"0\" $selected>All</option>\n");

    for ($i = 1; $i <= 10; $i++) {
        $selected = "";
        if ($i == $selectedValue) {
            $selected = "selected";
        }
        echo("<option value=\"$i\" $selected>$i</option>\n");
    }
    echo "</select> \n";
}

?>
    <div class="topnav">
        <form action="" method="post">
            &nbsp;<input type="text" value="<?php echo($input); ?>" name="q"/>
            <?php
            printSelect("types");
            printSelect("packages");
            printSelect("units");
            printSelect("projects");
            ?><label style="display: inline-block"><?php
            printSelect("locations");
            $sublocation = 0;
            if (isset($_POST["sublocation"])) {
                $sublocation = $_POST["sublocation"];
            }
            printSublocaton($sublocation, true);
            ?></label><?php
            $checked = "";
            if (isset($_POST["instock"])) {
                $checked = "checked";
            }
            ?>
            <label style="display: inline-block">
                <input id="checkbox_id" type="checkbox" name="instock" <?php echo("$checked"); ?> />
                op&nbsp;vooraad
            </label>
            <input name="" type="submit" value="Filters toepassen" style="float: right" />
        </form>
    </div>
<?php
$condition = "";

function addCondition($new)
{
    global $condition;
    if (strlen($condition) > 0) {
        $condition = $condition . " AND ";
    } else {
        $condition = " WHERE ";
    }
    $condition = $condition . $new;
}

if (strlen($input) > 0) {
    addCondition("parts.name LIKE \"%$input%\"");
}

function checkSelected($item)
{
    $itemname = "view-$item";
    if (isset($_POST[$itemname])) {
        $selectedValue = $_POST[$itemname];
        if ($selectedValue > 0) {
            addCondition("$item.id='$selectedValue'");
        }
    }
}

checkSelected("types");
checkSelected("packages");
checkSelected("units");
checkSelected("projects");
checkSelected("locations");
if ($sublocation > 0) {
    addCondition("parts.sublocation='$sublocation'");
}
if (isset($_POST["instock"])) {
    addCondition("parts.stock > 0");
}

$sql = "SELECT parts.id, parts.name, parts.description, parts.stock, parts.value, parts.sublocation,
       types.name as type, 
       units.name as unit,
       packages.name as package,
       locations.name as location,
       projects.name as project
        FROM parts 
        LEFT JOIN types ON parts.type=types.id
        LEFT JOIN units ON parts.unit=units.id
        LEFT JOIN packages ON parts.package=packages.id
        LEFT JOIN locations ON parts.location=locations.id
        LEFT JOIN partproject ON parts.id=partproject.part
        LEFT JOIN projects ON partproject.project=projects.id";

$sql = $sql . $condition;
$sql = $sql . " GROUP BY parts.id";
$sql = $sql . " ORDER BY parts.id ASC LIMIT 100";
// echo "<pre>$sql</pre>";
$result = $conn->query($sql);

$maxDesc = 25;
if ($result && $result->num_rows > 0) {
    ?>
    <br/>
    <table class="styled-table">
    <thead>
    <tr>
        <th>Naam</th>
        <th>Beschrijving</th>
        <th>Type</th>
        <th>Waarde</th>
        <th>Package</th>
        <th>Project</th>
        <th>Locatie</th>
        <th>Vooraad</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // output data of each row
    while ($row = $result->fetch_assoc()) {
        echo("<tr class='border'>\n");
        $id = $row["id"];
        echo("<td><a href='item.php?id=$id' >" . $row["name"] . "</a></td>\n");
        if ($row["description"]) {
            if (strlen($row["description"]) > $maxDesc) {
                echo("<td>" . substr($row["description"], 0, $maxDesc - 3) . "...</td>\n");
            } else {
                echo("<td>" . $row["description"] . "</td>\n");
            }
        } else {
            echo("<td></td>\n");
        }
        echo("<td>" . $row["type"] . "</td>\n");
        echo("<td>" . $row["value"] . " " . $row["unit"] . "</td>\n");
        echo("<td>" . $row["package"] . "</td>\n");
        echo("<td>" . $row["project"] . "</td>\n");
        if ($row["location"] != "-") {
            echo("<td>" . $row["location"] . " " . $row["sublocation"] . "</td>\n");
        } else {
            echo("<td></td>\n");
        }
        echo("<td>" . $row["stock"] . "</td>\n");


        echo("</tr>\n");
    }
    echo("    </tbody>\n</table>\n");
} else {
    echo "Geen componenten gevonden.";
}

?>

<?php
printFooter();