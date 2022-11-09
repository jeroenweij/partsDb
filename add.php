<?php

require('header.php');

printHeader("Toevoegen");

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
    $tag= validateInput($_POST["tag"]);

    $query = "INSERT INTO `parts` 
           (`name`,      `description`,  `type`,  `value`,   `stock`,  `package`,  `unit`,  `location`,  `sublocation`) 
    VALUES ('$partNum', '$description', '$type', '$value',  '$stock', '$package', '$unit', '$location', '$sublocation')";

    if ($conn->query($query)) {
        echo("<h3>$partNum is toegevoegd.</h3>");

        $result = $conn->query("SELECT MAX(id) as id FROM parts;");
        if ($result) {
            $row = $result->fetch_assoc();
            $partid = $row["id"];
            echo("<a href=\"item.php?id=$partid\">$partNum openen.</a>");

            // Add Tag
            if (strlen($tag)>2){
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
        }
    } else {
        echo("<h3>Toevoegen gefaald!</h3>");
    }
}


?>

    <h3>Toevoegen</h3>
    <form action="goadd.php" method="post">
        <input name="q" type="text" value=""/><br/>
        <input class="twohndrdpx" name="submit" type="submit" value="Toevoegen"/>
    </form>

<?php
printFooter();