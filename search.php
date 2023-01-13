<?php
if (!isset($_POST["q"]) || strlen($_POST["q"]) == 0) {
    header("Location: index.php");
    exit();
}
require('mysqlConn.php');
require('scan2id.php');
require('header.php');

$txt=$_POST['q'];
$input = scan2id(validateInput($_POST["q"]));

printHeader("Zoeken");


    $condition = "";
    $sql = "SELECT parts.id FROM parts WHERE parts.deleted=0 AND";

    $result = $conn->query("SELECT part FROM tags WHERE tag='$input'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $query = " parts.id = ".$row["part"];
        $result = $conn->query($sql . $query);
    }

    if ($result && $result->num_rows == 0) {
        if (str_starts_with($input, '*')) {
           $id = validateNumberInput($input);
           if (strlen($id) > 0 && strlen($id) < 5){
                $query = " parts.id = $id";
                $result = $conn->query($sql . $query);
           }
        }
    }

    if ($result && $result->num_rows == 0) {
        $query = " parts.name = \"$input\"";
        $result = $conn->query($sql . $query);
    }

    if ($result && $result->num_rows == 0) {
        $query = " parts.name LIKE \"%$input%\" OR parts.name LIKE \"%" . substr($input, 0, -1) . "%\"";
        $result = $conn->query($sql . $query);
    }

    if ($result && $result->num_rows == 0) {
        $query = " parts.description LIKE \"%$input%\" OR parts.description LIKE \"%" . substr($input, 0, -1) . "%\"";
        $result = $conn->query($sql . $query);
    }

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $form = "listForm";
            if ($result->num_rows == 1)
            {
                $form = "itemForm";
            }
        ?>

        <form id="listForm" action="list.php" method="post">
            <input type="hidden" name="q" value='<?php echo($input); ?>'>
        </form>
        <form id="itemForm" action="item.php" method="post">
            <input type="hidden" name="id" value="<?php echo($row["id"]); ?>">
        </form>
        <script type="text/javascript">
            document.getElementById('<?php echo($form); ?>').submit();
        </script>
        <?php
    } else {
        echo "Geen componenten gevonden met de term \"$input\".";
        ?>
        <br />
        <form action="search.php" method="post">
            <input name="q" type="text" value="" />&nbsp;
            <input class="twohndrdpx" name="submit" type="submit" value="Zoek" />
        </form>
    <?php
    }

printFooter();
