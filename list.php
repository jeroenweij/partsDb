<?php

require('mysqlConn.php');
require('scan2id.php');
require('header.php');

printHeader("Componenten");

$input = "";
if (isset($_POST["q"])) {
    $input = scan2id(validateInput($_POST["q"]));
}

$pageLimit = 50;
if (isset($_POST["pageLimit"])) {
    $pageLimit = $_POST["pageLimit"];
}

$pageNum = 0;
if (isset($_POST["pageNum"])) {
    $pageNum = $_POST["pageNum"];
}

function printFilterSelect($table, $name)
{
    global $conn;
    $itemname = "view-$table";
    $selectedValue = 0;
    if (isset($_POST[$itemname])) {
        $selectedValue = $_POST[$itemname];
    }

    echo("$name ");
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
            <input type="hidden" name="pageNum" value="<?php echo($pageNum); ?>"/>
            <input type="hidden" name="pageLimit" value="<?php echo($pageLimit); ?>"/>
            &nbsp;<input type="text" value="<?php echo($input); ?>" name="q"/>
            <?php
            printFilterSelect("types", "Type");
            printFilterSelect("packages", "Package");
            printFilterSelect("units", "Unit");
            printFilterSelect("projects", "Project");
            ?><label style="display: inline-block"><?php
                printFilterSelect("locations", "Lokatie");
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
                op&nbsp;voorraad
            </label>
            <input name="" type="submit" value="Filters toepassen" style="float: right"/>
        </form>
    </div>
<?php
$condition = "";

function addCondition($new)
{
    global $condition;
    $condition = $condition . " AND " . $new;
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
    addCondition("stock.sublocation='$sublocation'");
}
if (isset($_POST["instock"])) {
    addCondition("stock.count > 0");
}

$sql = "SELECT parts.id, parts.name, parts.description, parts.value, stock.sublocation,
       (SELECT SUM(count) FROM stock WHERE stock.partId=parts.id) as count,
       types.name as type, 
       units.name as unit,
       packages.name as package,
       locations.name as location,
       projects.name as project
        FROM parts 
        LEFT JOIN types ON parts.type=types.id
        LEFT JOIN units ON parts.unit=units.id
        LEFT JOIN packages ON parts.package=packages.id
        LEFT JOIN stock ON parts.id=stock.partId
        LEFT JOIN locations ON stock.location=locations.id
        LEFT JOIN partproject ON parts.id=partproject.part
        LEFT JOIN projects ON partproject.project=projects.id
        WHERE parts.deleted=0";

$count = $conn->query("SELECT count(*) as c FROM parts 
        LEFT JOIN types ON parts.type=types.id
        LEFT JOIN units ON parts.unit=units.id
        LEFT JOIN packages ON parts.package=packages.id
        LEFT JOIN stock ON parts.id=stock.partId
        LEFT JOIN locations ON stock.location=locations.id
        LEFT JOIN partproject ON parts.id=partproject.part
        LEFT JOIN projects ON partproject.project=projects.id
        WHERE parts.deleted=0 $condition");
if ($count) {
    $count = $count->fetch_assoc();
    $count = $count["c"];
}

if ($pageNum * $pageLimit > $count) {
    $pageNum = 0;
}

$sql = $sql . $condition;
$sql = $sql . " GROUP BY parts.id";
$sql = $sql . " ORDER BY parts.id ASC LIMIT $pageLimit";
$sql = $sql . " OFFSET " . ($pageLimit * $pageNum);


$result = $conn->query($sql);

$maxDesc = 25;
if ($result && $result->num_rows > 0) {
    ?>
    <br/>
    <table class="styled-table">
        <thead>
        <tr>
            <th>Id</th>
            <th>Naam</th>
            <th>Beschrijving</th>
            <th>Type</th>
            <th>Waarde</th>
            <th>Package</th>
            <th>Project</th>
            <th>Locatie</th>
            <th>Voorraad</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            echo("<tr class='border'>\n");
            $id = $row["id"];
            echo("<td><a href='item.php?id=$id' >$id</a></td>\n");
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
            echo("<td>" . $row["count"] . "</td>\n");


            echo("</tr>\n");
        }
        ?>
        </tbody>
    </table>
    Totaal: <?php echo($count); ?> onderdelen.
    <div style="float: right">
    <form action="" method="post">
        <?php
        function addFilter($name)
        {
            if (isset($_POST[$name])) {
                echo("<input type=\"hidden\" name=\"$name\" value=\"" . $_POST[$name] . "\" />\n");
            }
        }

        addFilter("q");
        addFilter("view-types");
        addFilter("view-packages");
        addFilter("view-units");
        addFilter("view-projects");
        addFilter("view-locations");
        addFilter("sublocation");
        addFilter("instock");

        if ($count > $pageLimit) {
            $pages = ceil($count / $pageLimit);
            echo('<label>Pagina<select name="pageNum">');
            for ($i = 0; $i < $pages; $i++) {
                if ($i == $pageNum) {
                    echo("<option selected>$i</option>\n");
                } else {
                    echo("<option>$i</option>\n");
                }
            }
            echo('</select></label>&nbsp;');
        } else {
            echo('<input name="pageNum" type="hidden" value="0" />');
        }
        ?>
        <label>
            Aantal per pagina:
            <select name="pageLimit">
                <?php
                $pagesizes = array(10, 25, 50, 100, 250, 500, 1000);
                foreach ($pagesizes as $i) {
                    if ($i == $pageLimit) {
                        echo("<option selected>$i</option>\n");
                    } else {
                        echo("<option>$i</option>\n");
                    }
                }
                ?>
            </select>
        </label>
        <input name="submit" type="submit" value="Ga"/>
    </form>
    </div>
    <?php

} else {
    echo "Geen componenten gevonden.";
}

?>

<?php
printFooter();
