<?php

require('mysqlConn.php');
require('header.php');
require('formFunctions.php');

printHeader("Nieuwe order");

if (isset($_POST["name"])) {
    $name = validateInput($_POST["name"]);
    $company = validateNumberInput($_POST["select-companys"]);
    $relation = validateNumberInput($_POST["select-relations"]);
    $conn->query("INSERT INTO `orders` (`name`, `company`, `relation`) VALUES ('$name', '$company', '$relation');");

    $result = $conn->query("SELECT MAX(id) as id FROM orders;");
    if ($result) {
        $row = $result->fetch_assoc();
        $id = $row["id"];
        header("Location: order.php?id=$id");
        exit();
    }
}

?>

    <form action="" method="post">
        <table>
            <tr>
                <td>Naam:</td>
                <td><input type="text" name="name"></td>
            </tr>
            <tr>
                <td>Bedrijf:</td>
                <td><?php printSelect("companys", 0);?></td>
            </tr>
            <tr>
                <td>Relatie:</td>
                <td><?php printSelect("relations", 0); ?></td>
            </tr>

        </table>
    <input type="submit" value="Opslaan">
    </form>

<?php
printFooter();