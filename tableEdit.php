<?php
if (!isset($table)) {
    header("Location: index.php");
    exit();
}

require('mysqlConn.php');
require('header.php');

if (isset($_POST["new"])) {
    $sql = "INSERT INTO `$table` (`name`) VALUES ('" . $_POST["new"] . "');";
    $conn->query($sql);
}


$title = ucfirst($table);
printHeader($title);
echo "<h2>$title</h2>\n";

$refdb= "parts";
$refname=substr($table, 0 ,-1);
if ($table == "projects"){
    $refdb= "partproject";
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
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = $result->fetch_assoc()) {
            echo("    <tr>\n");
            echo("        <td>" . $row["id"] . "</td>\n");
            echo("        <td>" . $row["name"] . "</td>\n");
            echo("        <td>" . $row["ref"] . "</td>\n");
            echo("    </tr>\n");
        }
        ?>
        </tbody>
    </table>

    <br/>
    <h2>Toevoegen</h2>
    <form action="<?php echo($table); ?>.php" method="post">
        <input name="new" type="text" value=""/><br/>
        <input class="twohndrdpx" name="submit" type="submit" value="Toevoegen"/>
    </form>
    <?php
}
?>

<?php
printFooter();
