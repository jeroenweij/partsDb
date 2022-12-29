<?php

$fileList = get_included_files();
$topfile = basename($fileList[0]);

function addLink($target, $name): void
{
    global $topfile;
    $class = "";
    if ($topfile == $target) {
        $class = 'class="active"';
    }
    echo("        <a $class href='$target'>$name</a>");
}

function printHeader($title, $extra = null): void
{
    echo("<!DOCTYPE html>\n");
    echo("<html>\n");
    echo("<head>\n");
    echo("    <meta charset=\"utf-8\">\n");
    echo("    <title>$title</title>\n");
    echo("    <link rel=\"stylesheet\" href=\"css/navbar.css\"/>\n");
    echo("    <link rel=\"stylesheet\" href=\"css/style.css\"/>\n");
    echo("    <link rel=\"shortcut icon\" href=\"favicon.ico\" type=\"image/x-icon\" />\n");

    if ($extra){
        echo($extra);
    }
    echo("</head>\n");
    echo("<body>\n");
    echo("<div class='form'>\n");

    ?>
    <div class="topnav">
        <?php
        addLink("index.php", "Home");
        addLink("list.php", "Inzien");
        addLink("projects.php", "Projecten");
        addLink("locations.php", "Locaties");
        addLink("types.php", "Types");
        addLink("units.php", "Units");
        addLink("add.php", "Toevoegen");
        addLink("orders.php", "Orders");
        ?>
        <div class="search-container">
            <form action="search.php" method="post">
                <input type="text" placeholder="Zoek.." name="q">
                <input name="submit" type="submit" value="Ga"/>
            </form>
        </div>
    </div>
    <?php
    echo "<h2>$title</h2>\n";
}

function printFooter(): void
{
    global $conn;
    echo("</div>");
    echo("</body>");
    echo('</html>');

    if (isset($conn)){
        $conn->close();
    }
}

?>