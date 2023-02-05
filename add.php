<?php

require('header.php');
require('label/labelform.php');

printHeader("Toevoegen", $labelformscript);

// If adding a new part
if (isset($_POST["mpn"])) {
    require('mysqlConn.php');

    $partNum = substr(validateInput($_POST["mpn"]), 0, 80);
    $description = substr(validateInput($_POST["desc"]), 0, 120);
    $type = validateNumberInput($_POST["select-types"]);
    $value = validateNumberInput($_POST["value"]);
    $stock = validateNumberInput($_POST["stock"]);
    $package = validateNumberInput($_POST["select-packages"]);
    $unit = validateNumberInput($_POST["select-units"]);
    $location = validateNumberInput($_POST["select-locations"]);
    $sublocation = validateNumberInput($_POST["sublocation"]);
    $tag = validateInput($_POST["tag"]);

    $query = "INSERT INTO `parts` 
           (`name`, `description`,  `type`,  `value`,  `package`,  `unit`) 
    VALUES ('$partNum', '$description', '$type', '$value', '$package', '$unit')";

    if ($conn->query($query)) {
        echo("<h3>$partNum is toegevoegd.</h3>");

        $result = $conn->query("SELECT MAX(id) as id FROM parts;");
        if ($result) {
            $row = $result->fetch_assoc();
            $partid = $row["id"];
            echo("<a href=\"item.php?id=$partid\">$partNum openen.</a>");

            // Add stock
            if ($stock > 0){
                $conn->query("INSERT INTO `stock` (`partId`, `location`, `sublocation`, `count`) VALUES ('$partid', '$location', '$sublocation', '$stock');");
            }

            // Add Tag
            if (strlen($tag) > 2) {
                $conn->query("INSERT INTO `tags` (`tag`, `part`) VALUES ('$tag', '$partid');");
            }

            // Add projects
            $projectcount = $_POST["projectcount"];
            for ($i = 0; $i < $projectcount; $i++) {
                $projectid = $_POST["projectid-$i"];
                $usecount = validateNumberInput($_POST["project-$i"]);
                if ($usecount > 0) {
                    $conn->query("INSERT INTO `partproject` (`part`, `project`, `count`) VALUES ('$partid', '$projectid', '$usecount');");
                }
            }

            printprintbutton($partid, $partNum, $type, "$value $unit","$location $sublocation");
        }
    } else {
        echo("<h3 style='color: #ff0000'>Toevoegen gefaald!</h3>");
    }
}


?>
    <p>Voeg een nieuw onderdeel toe aan de database, Vul de naam van het onderdeel in.</p>
    <form action="goadd.php" method="post">
        <input name="q" type="text" value="" autofocus />&nbsp;
        <input class="twohndrdpx" name="submit" type="submit" value="Toevoegen"/>
    </form>

<?php
printFooter();