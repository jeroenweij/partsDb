<?php
function printSelect($table, $selectedValue)
{
    global $conn;
    echo("<select name=\"select-$table\">\n");
    $stresult = $conn->query("SELECT DISTINCT name, id FROM $table ORDER BY name ASC");
    if ($stresult && $stresult->num_rows > 0) {
        // output data of each row
        while ($strow = $stresult->fetch_assoc()) {
            $selected = "";
            if ($strow["id"] == $selectedValue) {
                $selected = "selected";
            }
            echo("<option value=\"" . $strow["id"] . "\" $selected>" . $strow["name"] . "</option>\n");
        }
    }
    echo "</select> \n";
}
function printSublocaton($selectedValue){
    echo("<select name=\"sublocation\">\n");
    for ($i=1; $i<=10; $i++){
        $selected = "";
        if ($i == $selectedValue) {
            $selected = "selected";
        }
        echo("<option value=\"$i\" $selected>$i</option>\n");
    }
    echo "</select> \n";
}