<?php
// Database connection
$servername = "145.239.236.240";
$username = "srv80132";
$password = "OAmT8rD6RL";
$dbname = "srv80132";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'Infections';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

$offset = ($page - 1) * $limit;

// Define column mapping for sorting
$column_map = [
    'Nick' => 'sql_mainstats.Nick',
    'Infections' => 'sql_mainstats.Infections',
    'Kills' => 'sql_mainstats.Kills',
    'Time' => 'sql_times.`Time Played`',
    'LastSeen' => 'sql_times.`Last Seen`'
];

// Prepare SQL query
$sql = "SELECT
        sql_mainstats.Nick,
        sql_mainstats.Infections,
        sql_mainstats.Kills,
        sql_times.`Time Played` AS `Time`,
        sql_times.`Last Seen` AS `Last`
    FROM
        sql_mainstats
    INNER JOIN
        sql_times
    ON
        sql_mainstats.Auth = sql_times.Auth";

if ($search !== '') {
    $sql .= " WHERE sql_mainstats.Nick LIKE ?";
}

$sql .= " ORDER BY " . $column_map[$sort_column] . " " . $sort_order . " LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if ($search !== '') {
    $search_param = "%$search%";
    $stmt->bind_param("sii", $search_param, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$players = array();
if ($result->num_rows > 0) {
    $rank = $offset + 1;
    while($row = $result->fetch_assoc()) {
        $row['rank'] = $rank++;
        $players[] = $row;
    }
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM sql_mainstats";
if ($search !== '') {
    $countSql .= " WHERE Nick LIKE ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param("s", $search_param);
} else {
    $stmt = $conn->prepare($countSql);
}
$stmt->execute();
$countResult = $stmt->get_result();
$totalPlayers = $countResult->fetch_assoc()['total'];

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode(array(
    'players' => $players,
    'totalPlayers' => $totalPlayers
));