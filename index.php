<?php

require('header.php');

printHeader("Componenten");
?>
    <h3>Opzoeken.</h3>

    <form action="search.php" method="post">
    <input name="q" type="text" value="" />&nbsp;
    <input class="twohndrdpx" name="submit" type="submit" value="Zoek" />
    </form>

    <h3>Inzien.</h3>
    <form action="list.php">
        <input class="twohndrdpx" name="" type="submit" value="Alles inzien"/>
    </form>

    <h3>Projecten.</h3>
    <form action="projects.php">
        <input class="twohndrdpx" name="" type="submit" value="Projecten"/>
    </form>

    <h3>Toevoegen.</h3>
    <form action="goadd.php" method="post">
        <input name="q" type="text" value="" />&nbsp;
        <input class="twohndrdpx" name="submit" type="submit" value="Toevoegen" />
    </form>

<?php
printFooter();
