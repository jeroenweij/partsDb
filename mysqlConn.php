<?php
$servername = "localhost";
$username = "Username";
$password = "Password";
$db = "components";

// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function validateInput($input)
{
    global $conn;
    $text=$conn->real_escape_string(stripslashes(trim($input)));
    return strip_tags($text, '<p><b><br><ul><li><ol>');
}

function validateNumberInput($input)
{
    $input = validateInput($input);
    return preg_replace("/[^0-9\\.]/", '', $input);
}

function MysqlQuery($conn, $query)
{
    $md=md5($query);
    if (isset($_SESSION['lastquery'])){
        if ($_SESSION['lastquery'] === $md){
            return null;
        }
    }
    $_SESSION['lastquery'] = $md;
    return $conn->query($query);
}

?>
