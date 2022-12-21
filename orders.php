<?php

require('mysqlConn.php');
require('header.php');

printHeader("Orders");

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


$sql = "SELECT orders.id, orders.name,
       companys.name as company,
       relations.name as relation,
       statuses.name as status
        FROM orders 
        LEFT JOIN relations ON orders.relation=relations.id
        LEFT JOIN companys ON orders.company=companys.id
        LEFT JOIN statuses ON orders.status=statuses.id";

$count = $conn->query("SELECT count(*) as c FROM orders");
if ($count) {
    $count = $count->fetch_assoc();
    $count = $count["c"];
}

if ($pageNum * $pageLimit > $count) {
    $pageNum = 0;
}

$sql = $sql . " ORDER BY orders.id ASC LIMIT $pageLimit";
$sql = $sql . " OFFSET " . ($pageLimit * $pageNum);

$result = $conn->query($sql);

$maxLen = 25;
if ($result && $result->num_rows > 0) {
    ?>
        <a href="neworder.php">Nieuwe order</a>
    <br/>
    <table class="styled-table">
        <thead>
        <tr>
            <th>Id</th>
            <th>Beschrijving</th>
            <th>BV</th>
            <th>Relatie</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            echo("<tr class='border'>\n");
            $id = $row["id"];
            echo("<td><a href='order.php?id=$id' >$id</a></td>\n");
            if ($row["name"]) {
                if (strlen($row["name"]) > $maxLen) {
                    echo("<td><a href='order.php?id=$id' >" . substr($row["name"], 0, $maxLen - 3) . "...</a></td>\n");
                } else {
                    echo("<td><a href='order.php?id=$id' >" . $row["name"] . "</a></td>\n");
                }
            } else {
                echo("<td></td>\n");
            }
            echo("<td>" . $row["company"] . "</td>\n");
            echo("<td>" . $row["relation"] . "</td>\n");
            echo("<td>" . $row["status"] . "</td>\n");

            echo("</tr>\n");
        }
        ?>
        </tbody>
    </table>
    Totaal: <?php echo($count); ?> orders.
    <div style="float: right">
    <form action="" method="post">
        <?php
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
    echo "Geen orders.";
}

?>

<?php
printFooter();