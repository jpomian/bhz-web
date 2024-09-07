<?php
require 'vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection using environment variables
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search query from the query string
$query = isset($_GET['q']) ? $_GET['q'] : '';

// Prepare and execute the SQL query to search for nicknames
$stmt = $conn->prepare("
    SELECT
        sql_mainstats.Nick,
        sql_mainstats.Infections,
        sql_mainstats.Kills,
        CEIL(sql_times.`Time Played` / 60) AS `Time Played (minutes)`,
        sql_times.`Last Seen`
    FROM
        sql_mainstats
    INNER JOIN
        sql_times
    ON
        sql_mainstats.Auth = sql_times.Auth
    WHERE
        sql_mainstats.Nick LIKE ?
    ORDER BY
        sql_mainstats.Infections DESC
");
$search_query = '%' . $query . '%'; 
$stmt->bind_param("s", $search_query);
$stmt->execute();
$result = $stmt->get_result();

// Display the search results
echo "<table border='1'>
<tr>
<th>Gracz</th>
<th>Infekcje</th>
<th>Zabójstwa</th>
<th>Czas gry</th>
<th>Ostatnio grał</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Nick'] . "</td>";
    echo "<td>" . $row['Infections'] . "</td>";
    echo "<td>" . $row['Kills'] . "</td>";
    echo "<td>" . $row['Time Played (minutes)'] . "</td>";
    echo "<td>" . timeAgo($row['Last Seen']) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Close the database connection
$stmt->close();
$conn->close();

// Function to calculate time ago
function timeAgo($timestamp) {
    $now = new DateTime();
    $lastSeen = new DateTime("@$timestamp");
    $interval = $now->diff($lastSeen);

    if ($interval->days > 0) {
        return $interval->days . ' days ago';
    } elseif ($interval->h > 0) {
        return $interval->h . ' hours ago';
    } else {
        return $interval->i . ' minutes ago';
    }
}
?>
