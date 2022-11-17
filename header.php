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

function printHeader($title): void
{
    echo("<!DOCTYPE html>");
    echo('<html>');
    echo('<head>');
    echo('    <meta charset="utf-8">');
    echo("    <title>$title</title>");
    echo('    <link rel="stylesheet" href="css/navbar.css"/>');
    echo('    <link rel="stylesheet" href="css/style.css"/>');
    echo("</head>");
    echo("<body>");
    echo("<div class='form'>");

    ?>
    <div class="topnav">
        <?php
        addLink("index.php", "Home");
        addLink("list.php", "Inzien");
        addLink("projects.php", "Projecten");
        addLink("locations.php", "Locaties");
        addLink("types.php", "Types");
        addLink("add.php", "Toevoegen");
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
    echo("</div>");
    echo("</body>");
    echo('</html>');
}

?>