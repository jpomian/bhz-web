<?php
// Database connection details
$servername = "sql.pukawka.pl";
$username = "898035";
$password = "xxx";
$database = "898035_czasy";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['search'])) {
    $search = $_GET['search'];

    // SQL query to retrieve matching players by nickname
    $sql = "SELECT `Nick`, `Time Played` FROM TimePlayed WHERE `Nick` LIKE '%$search%' ORDER BY `Time Played` DESC";
    $result = $conn->query($sql);

    $results = array();

    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    echo json_encode($results);
}

// Close the database connection
$conn->close();
?>