<?php

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
        <a class="active" href="index.php">Home</a>
        <a href="list.php">Inzien</a>
        <a href="projects.php">Projecten</a>
        <a href="locations.php">Locaties</a>
        <a href="types.php">Types</a>
        <a href="add.php">Toevoegen</a>
        <div class="search-container">
            <form action="search.php" method="post">
                <input type="text" placeholder="Zoek.." name="q">
                <input name="submit" type="submit" value="Ga" />
            </form>
        </div>
    </div>
    <?php
}

function printFooter(): void
{
    echo("</div>");
    echo("</body>");
    echo('</html>');
}
?>