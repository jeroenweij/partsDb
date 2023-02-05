<?php

require('scan2id.php');
require('nexarAPI.php');
require('header.php');
require('mysqlConn.php');

printHeader("Nexar");

?>
    <form action="nexar.php" method="post">
        <input name="q" type="text" value=""/>&nbsp;
        <input class="twohndrdpx" name="submit" type="submit" value="Zoek"/>
    </form>
<?php


function isMatch($parent, $category, $specName)
{
    echo("<pre>Parent = $parent && spec = $specName</pre>\n");
    if ($specName == "Inductance" && $parent == "inductors")
        return true;
    if ($specName == "Resistance" && $parent == "resistors")
        return true;
    if ($specName == "Capacitance" && $parent == "capacitors")
        return true;
    if ($specName == "Output Current" && ($parent == "power-management-ics" || $parent == "linear-ics"))
        return true;
    if ($specName == "Output Power" && $parent == "linear-ics")
        return true;
    if ($specName == "Density" && ($parent == "memory" || $parent == "embedded-processors-and-controllers"))
        return true;
    if ($parent == "emi-rfi-components") {
        return ($specName == "DC Resistance (DCR)" || $specName == "Coil Resistance");
    }
    if ($parent == "crystals-and-oscillators") {
        return ($specName == "Frequency");
    }
    if ($parent == "transistors") {
        return ($specName == "Continuous Drain Current (ID)" || $specName == "Max Collector Current");
    }
    echo("<pre>FALSE</pre>\n");
    return false;
}

if (isset($_POST["q"])) {
    $txt = $_POST['q'];
    $input = scan2id(validateInput($_POST["q"]));
    $nexarData = json_decode(nexarQuery($input), true);
    echo("<br />\n");
    echo("<pre>\n");
    print_r($nexarData);
    echo("</pre>\n");
    echo("\n");

    $mpn = $input;
    $description = "";
    $category = "-";
    $parent = "-";
    $value = "0";
    $valueUnit = "-";
    $package = "-";

    if (array_key_exists("data", $nexarData)) {
        $nexarData = $nexarData["data"];
        if (array_key_exists("supSearch", $nexarData)) {
            $nexarData = $nexarData["supSearch"];
            if (array_key_exists("results", $nexarData)) {
                $nexarData = $nexarData["results"];
                if ($nexarData && count($nexarData) > 0) {
                    $nexarData = $nexarData[0];
                    if (array_key_exists("part", $nexarData)) {
                        $nexarData = $nexarData["part"];
                        if (array_key_exists("mpn", $nexarData)) {
                            $mpn = $nexarData["mpn"];
                        }
                        if (array_key_exists("shortDescription", $nexarData)) {
                            $description = $nexarData["shortDescription"];
                        }
                        if (array_key_exists("category", $nexarData)) {
                            $catArray = $nexarData["category"];
                            if ($catArray && array_key_exists("name", $catArray)) {
                                $category = $catArray["name"];
                                $catpath = explode("/", $catArray["path"]);
                                if (count($catpath) > 2) {
                                    $parent = $catpath[count($catpath) - 2];
                                }
                            } else {
                                if (str_contains($description, "Res")) {
                                    $category = "Resistor";
                                } else if (str_contains($description, "Cap")) {
                                    $category = "Capacitor";
                                }
                            }
                        }

                        // Continue with specs
                        $valueSet = false;
                        $packageSet = false;
                        if (array_key_exists("specs", $nexarData)) {
                            $specs = $nexarData["specs"];
                            for ($i = 0; $i < count($specs); $i++) {
                                $spec = $specs[$i];

                                if (!$valueSet && isMatch($parent, $category, $spec["attribute"]["name"])) {
                                    if ($spec["valueType"] == "number" || $spec["valueType"] == "float") {
                                        $value = $spec["value"];
                                        $valueUnit = $spec["units"];
                                        if ($packageSet)
                                            break;
                                        $valueSet = true;
                                    }
                                }

                                if (!$packageSet) {
                                    if ($spec["attribute"]["name"] == "Case/Package") {
                                        $package = $spec["value"];
                                        if ($valueSet)
                                            break;
                                        $packageSet = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    ?>
        <table>
            <tr>
                <td>$mpn</td>
                <td><?php echo($mpn);?></td>
            </tr>
            <tr>
                <td>$description</td>
                <td><?php echo($description);?></td>
            </tr>
            <tr>
                <td>$category</td>
                <td><?php echo($category);?></td>
            </tr>
            <tr>
                <td>$parent</td>
                <td><?php echo($parent);?></td>
            </tr>
            <tr>
                <td>$value</td>
                <td><?php echo($value);?></td>
            </tr>
            <tr>
                <td>$valueUnit</td>
                <td><?php echo($valueUnit);?></td>
            </tr>
            <tr>
                <td>$package</td>
                <td><?php echo($package);?></td>
            </tr>
        </table>

<?php
}

printFooter();
