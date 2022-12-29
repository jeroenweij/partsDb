<?php
// Database configuration
require('mysqlConn.php');

// Create database connection
// Get search term
$searchTerm = $_GET['term'];

// Fetch matched data from the database
$query = $conn->query("SELECT id, name FROM parts WHERE name LIKE '%".$searchTerm."%' AND deleted = 0 ORDER BY name ASC LIMIT 10");

// Generate array with skills data
$skillData = array();
if($query->num_rows > 0){
    while($row = $query->fetch_assoc()){
        $data['id'] = $row['id'];
        $data['value'] = $row['name'];
        array_push($skillData, $data);
    }
}

// Return results as json encoded array
echo json_encode($skillData);
?>