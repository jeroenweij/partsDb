<?php

function isMatch($parent, $category, $specName, $debug=false)
{
    if ($debug)
        echo("<pre>Parent = $parent && spec = $specName</pre>\n");
    if ($specName == "Inductance" && $parent == "inductors")
        return true;
    if ($specName == "Resistance" && ($parent == "resistors" || $category=="Resistor"))
        return true;
    if ($specName == "Capacitance" && ($parent == "capacitors" || $category=="Capacitor" || $category=="Capacitors"))
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
    if ($parent == "connectors") {
        return ($specName == "Number of Pins");
    }
    if ($debug)
        echo("<pre>FALSE</pre>\n");
    return false;
}

?>
