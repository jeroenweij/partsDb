<?php

require('header.php');

printHeader("Stock update");

require('mysqlConn.php');

$query = "SELECT id, location, sublocation,stock FROM `parts`";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {

        $id = $row["id"];
        $count = $row["stock"];
        $location = $row["location"];
        $subloc = $row["sublocation"];

        echo("<a href = 'item.php?id=$id' >$id - " . $row["stock"] . "</a ><br> \n");

        $conn->query("INSERT INTO stock (partId, location, sublocation, count) SELECT $id, $location, $subloc, $count
        FROM DUAL WHERE NOT EXISTS (
        SELECT count FROM stock WHERE partId = $id AND location = $location AND sublocation = $subloc);");
    }

} else {
    echo(" < h3 style = 'color: #ff0000' > FAIL!</h3 > ");
}


?>
<?php
printFooter();