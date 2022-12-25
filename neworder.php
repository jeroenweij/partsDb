<?php

require('mysqlConn.php');
require('header.php');
require('formFunctions.php');

printHeader("Nieuwe order");

if (isset($_POST["name"])) {
    $name = validateInput($_POST["name"]);
    $company = validateNumberInput($_POST["select-companys"]);
    $relation = validateNumberInput($_POST["select-relations"]);

    if (isset($_POST["edit-id"])) {
        $id = $_POST["edit-id"];
        $conn->query("UPDATE `orders` SET `name`='$name', `company`='$company', `relation`='$relation' WHERE id=$id;");
    }else {
        $conn->query("INSERT INTO `orders` (`name`, `company`, `relation`) VALUES ('$name', '$company', '$relation');");

        $result = $conn->query("SELECT MAX(id) as id FROM orders;");
        if ($result) {
            $row = $result->fetch_assoc();
            $id = $row["id"];
        }
    }
    header("Location: order.php?id=$id");
    exit();
}

?>

    <form action="" method="post">
        <?php
        $name = "";
        $company = 0;
        $relation = 0;
        if (isset($_POST["edit-id"])) {
            $editid = $_POST["edit-id"];
            $name = $_POST["oldname"];
            $company = $_POST["oldcompany"];
            $relation = $_POST["oldrelation"];
            echo("<input type='hidden' name='edit-id' value='$editid' />");
        }
        ?>
        <table>
            <tr>
                <td>Naam:</td>
                <td><input type="text" name="name" value="<?php echo("$name"); ?>" /></td>
            </tr>
            <tr>
                <td>Bedrijf:</td>
                <td><?php printSelect("companys", $company); ?></td>
            </tr>
            <tr>
                <td>Relatie:</td>
                <td><?php printSelect("relations", $relation); ?></td>
            </tr>

        </table>
        <input type="submit" value="Opslaan">
    </form>

<?php
printFooter();