<?php

require_once('mysqlConn.php');
require_once('scan2id.php');
require_once('header.php');

printHeader("Componenten");

$input = filter_input(INPUT_POST, 'q');
if (!empty($input)) {
    $input = scan2id(validateInput($input));
}

$pageLimit = 50;
$pageNum = 0;
$sublocation = 0;
$instock = false;
$ordervalue = false;

if (array_key_exists('pageLimit', $_POST)) {
    $pageLimit = filter_input(INPUT_POST, 'pageLimit', FILTER_SANITIZE_NUMBER_INT);
}

if (array_key_exists('pageNum', $_POST)) {
    $pageNum = filter_input(INPUT_POST, 'pageNum', FILTER_SANITIZE_NUMBER_INT);
}

if (array_key_exists('sublocation', $_POST)) {
    $sublocation = filter_input(INPUT_POST, 'sublocation', FILTER_SANITIZE_NUMBER_INT);
}

if (array_key_exists('instock', $_POST)) {
    $instock = filter_input(INPUT_POST, 'instock', FILTER_VALIDATE_BOOLEAN);
}
if (array_key_exists('order-value', $_POST)) {
    $ordervalue = filter_input(INPUT_POST, 'order-value', FILTER_VALIDATE_BOOLEAN);
}

function printFilterSelect($table, $name)
{
    global $conn;
    $itemname = "view-$table";
    $selectedValue = 0;
    if (array_key_exists($itemname, $_POST)) {
        $selectedValue = filter_input(INPUT_POST, $itemname, FILTER_SANITIZE_NUMBER_INT);
    }

    echo("<label style=\"display: inline-block\">\n");
    echo("$name ");
    echo "<select name=\"$itemname\">\n";
    echo "<option value=\"0\">All</option>\n";
    $stmt = $conn->prepare("SELECT DISTINCT name, id FROM $table ORDER BY name");
    $stmt->execute();
    $stmt->bind_result($name, $id);
    while ($stmt->fetch()) {
        $selected = ($id == $selectedValue) ? 'selected' : '';
        echo "<option value=\"$id\" $selected>$name</option>\n";
    }
    $stmt->close();
    echo "</select>\n";
    echo("</label>\n");
}

function printSublocaton($selectedValue)
{
    echo("<select name=\"sublocation\">\n");
    echo("<option value=\"0\">-</option>\n");
    for ($i = 1; $i <= 10; $i++) {
        $selected = ($i == $selectedValue) ? 'selected' : '';
        echo("<option value=\"$i\" $selected>$i</option>\n");
    }
    echo "</select>\n";
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
                printSublocaton($sublocation);
                ?></label><?php
            $checked = "";
            if (isset($_POST["instock"])) {
                $checked = "checked";
            }
            ?>
            <label style="display: inline-block">
                <input type="checkbox" name="instock" <?php echo("$checked"); ?> />
                op&nbsp;voorraad
            </label>
            <label style="display: inline-block"><?php
                $checked = "";
                if (isset($_POST["order-value"])) {
                    $checked = "checked";
                }
                ?>
                <input type="checkbox" name="order-value" <?php echo("$checked"); ?> />
                sorteer waarde
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

if (!empty($input)) {
    addCondition("parts.name LIKE \"%$input%\" OR parts.description LIKE \"%$input%\"");
}

function checkSelected($item)
{
    $itemname = "view-$item";
    $selectedValue = filter_input(INPUT_POST, $itemname, FILTER_SANITIZE_NUMBER_INT);
    if ($selectedValue > 0) {
        addCondition("$item.id='$selectedValue'");
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

$select = "parts.id, parts.name, parts.description, parts.value, stock.sublocation,
       (SELECT SUM(count) FROM stock WHERE stock.partId=parts.id) as count,
       types.name as type, 
       units.name as unit,
       packages.name as package,
       locations.name as location,
       projects.name as project";

$from = "parts 
        LEFT JOIN types ON parts.type=types.id
        LEFT JOIN units ON parts.unit=units.id
        LEFT JOIN packages ON parts.package=packages.id
        LEFT JOIN stock ON parts.id=stock.partId
        LEFT JOIN locations ON stock.location=locations.id
        LEFT JOIN partproject ON parts.id=partproject.part
        LEFT JOIN projects ON partproject.project=projects.id";

$where = "parts.deleted=0 AND (stock.deleted=0 OR stock.deleted IS NULL) $condition";

$sql = "SELECT $select FROM $from WHERE $where";

$count_query = "SELECT COUNT(DISTINCT(parts.id)) as c FROM $from WHERE $where";

$stmt = $conn->prepare($count_query);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($pageNum * $pageLimit > $count) {
    $pageNum = 0;
}

$order = "parts.id";
if ($ordervalue) {
    $order = "parts.value";
}
$sql = $sql . " GROUP BY parts.id ORDER BY $order ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$offset = $pageNum * $pageLimit;
$stmt->bind_param("ii", $pageLimit, $offset);
$stmt->execute();
$result = $stmt->get_result();

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
            addFilter("order-value");

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
