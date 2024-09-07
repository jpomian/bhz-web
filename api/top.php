<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url(https://fonts.googleapis.com/css?family=Open+Sans:300,400,700);
        body {
            font-family: 'Open Sans', sans-serif;
            font-weight: 300;
            line-height: 1.42em;
            text-align: center;
            background-color: #333;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .supreme {
            color: rgb(223, 56, 19);
            margin: 10px 15px;
            font-weight: bold;
            font-size: 30px;
        }

        .submain {
            color: rgb(206, 45, 27);
            margin: 12px 15px;
            font-weight: bold;
            font-size: 14px;
        }

        table {
            margin-left: auto;
            margin-right: auto;
            width: 42%;
            border-collapse: collapse;
        }

        th {
            background-color: #222;
            color: #fff;
        }

        th, td {
            padding: 10px;
            border: 1px solid #555;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #444;
        }

        tr:hover {
            background-color: #464A52;
        }

        .gold {
            color: gold; /* Change the text color to gold for gold players */
            font-weight: bold; /* Make the text bold for gold players */
            }

        .silver {
            color: silver; /* Change the text color to silver for silver players */
            text-shadow: 1px 1px;
        }

        .bronze {
            color: #cd7f32; /* Change the text color to bronze color for bronze players */
            font-weight: bold; /* Make the text bold for bronze players */
        }

        a {
            text-decoration: none;
            color: #fff;
            font-size: 14px;
        }

        .page-number {
            margin: 0px 5px; 
            font-size: 14px;
        }

        button {
            font-weight: bold;
            background-color: #444;
            color: #fff;
            border: none;
            padding: 10px 20px;
            margin: 20px 5px 10px;
            cursor: pointer;
        }

        button:hover {
            background-color: #555;
        }

        #searchInput, .page-number {
        padding: 10px;
        font-size: 16px;
        border: 2px solid #444; /* Darker border color */
        border-radius: 5px;
        margin-bottom: 10px;
        width: 300px; /* Adjust the width as needed */
        background-color: #333; /* Dark background color */
        color: #fff; /* White text color */
        }

        /* Styling for the search button (if you have one) */
        #search-button {
            padding: 10px 20px;
            background-color: #444; /* Dark button background color */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        /* Style the search container */
        #search-container {
            margin: 10px 5px;
            text-align: center;
        }
    </style>
    <title>Statystyki: Zombie</title>
</head>

<body>
    <h1 class="supreme">Najwięksi wymiatacze</h1>
    <h2 class="submain">Serwer: Zombie Biohazard</h2>
    <?php
    require 'vendor/autoload.php';

    // Load environment variables from .env file
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Database connection using environment variabless
    $servername = $_ENV['DB_HOST'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];
    $dbname = $_ENV['DB_NAME'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Number of records per page
    $records_per_page = 20;

    // Get the current page number from the query string, default to 1
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

    $showTable = true; // Set this variable to control table visibility

    // Check if there's a search query, and hide the table if needed
    if (isset($_GET['q'])) {
        $showTable = false;
    }

    // Get the sorting column and order from the query string, default to 'Time' descending
    $sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'Time';
    $sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

    // Calculate the offset
    $offset = ($page - 1) * $records_per_page;

    // Map sort column names to database column names
    $column_map = [
        'Nick' => 'sql_mainstats.Nick',
        'Infections' => 'sql_mainstats.Infections',
        'Kills' => 'sql_mainstats.Kills',
        'Time' => 'sql_times.`Time Played`',
        'Last Seen' => 'sql_times.`Last Seen`'
    ];

    // Ensure the sort column is valid
    if (!array_key_exists($sort_column, $column_map)) {
        $sort_column = 'Time';
    }

    // Prepare and execute the SQL query with pagination and sorting
    $stmt = $conn->prepare("
    SELECT
        sql_mainstats.Nick,
        sql_mainstats.Infections,
        sql_mainstats.Kills,
        sql_times.`Time Played` AS `Time`,
        sql_times.`Last Seen`
    FROM
        sql_mainstats
    INNER JOIN
        sql_times
    ON
        sql_mainstats.Auth = sql_times.Auth
    ORDER BY
        " . $column_map[$sort_column] . " " . $sort_order . "
    LIMIT ? OFFSET ?
");
    $stmt->bind_param("ii", $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the total number of records
    $total_records_stmt = $conn->query("
    SELECT COUNT(*) AS total
    FROM
        sql_mainstats
    INNER JOIN
        sql_times
    ON
        sql_mainstats.Auth = sql_times.Auth
");
    $total_records = $total_records_stmt->fetch_assoc()['total'];

    // Calculate the total number of pages
    $total_pages = ceil($total_records / $records_per_page);

    // Function to calculate time ago
    function timeAgo($timestamp)
    {
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

    function formatTime($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
    
        if ($hours < 1) {
            return sprintf("%02dmin", $minutes);
        } else {
            return sprintf("%dh:%02dm", $hours, $minutes);
        }
    }
    

    // Display the search input fieldd
    echo "<input type='text' id='searchInput' placeholder='Znajdź gracza...' onkeyup='searchNicknames()'>";
    echo "<div id='searchResults'></div>";

    if ($showTable) {
        echo "<table border='1' id='mainTable'>
    <tr>
    <th>Pozycja</th>
    <th>Gracz</th>
    <th><a href='?sort=Infections&order=" . ($sort_column == 'Infections' && $sort_order == 'DESC' ? 'ASC' : 'DESC') . "&page=$page'>Infekcje " . ($sort_column == 'Infections' ? ($sort_order == 'ASC' ? '↑' : '↓') : '') . "</a></th>
    <th><a href='?sort=Kills&order=" . ($sort_column == 'Kills' && $sort_order == 'DESC' ? 'ASC' : 'DESC') . "&page=$page'>Zabójstwa " . ($sort_column == 'Kills' ? ($sort_order == 'ASC' ? '↑' : '↓') : '') . "</a></th>
    <th><a href='?sort=Time&order=" . ($sort_column == 'Time' && $sort_order == 'DESC' ? 'ASC' : 'DESC') . "&page=$page'>Czas gry " . ($sort_column == 'Time' ? ($sort_order == 'ASC' ? '↑' : '↓') : '') . "</a></th>
    <th><a href='?sort=Last Seen&order=" . ($sort_column == 'Last Seen' && $sort_order == 'DESC' ? 'ASC' : 'DESC') . "&page=$page'>Ostatnio grał " . ($sort_column == 'Last Seen' ? ($sort_order == 'ASC' ? '↑' : '↓') : '') . "</a></th>
    </tr>";

        $rank = ($page - 1) * $records_per_page + 1; // Calculate the starting rank for the current page

        while ($row = $result->fetch_assoc()) {
            $rankColor = '';
            if ($rank == 1) {
                $rankColor = 'style="color: gold;"';
            } elseif ($rank == 2) {
                $rankColor = 'style="color: silver;"';
            } elseif ($rank == 3) {
                $rankColor = 'style="color: #CD7F32;"'; // Bronze color
            }

            echo "<tr>";
            echo "<td>$rank</td>";
            echo "<td $rankColor>" . $row['Nick'] . "</td>";
            echo "<td>" . $row['Infections'] . "</td>";
            echo "<td>" . $row['Kills'] . "</td>";
            echo "<td>" . formatTime($row['Time']) . "</td>";
            echo "<td>" . timeAgo($row['Last Seen']) . "</td>";
            echo "</tr>";

            $rank++;
        }

        echo "</table>";
    }

    // Display the navigation
    echo "<div>";
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $page) {
            echo "<span>$i</span> ";
        } else {
            echo "<a href='?page=$i&sort=$sort_column&order=$sort_order'>$i</a> ";
        }
    }
    echo "</div>";

    echo "<a href='../index.html'><button>Powrót</button></a>";

    // Close the database connection
    $stmt->close();
    $conn->close();
    ?>

    <script>
        function searchNicknames() {
            var input = document.getElementById('searchInput').value;
            var table = document.getElementById('mainTable');

            if (input.length >= 3) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'search.php?q=' + input, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById('searchResults').innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
                table.style.display = 'none'; // Hide the table
            } else {
                document.getElementById('searchResults').innerHTML = '';
                table.style.display = ''; // Show the table
            }
        }
    </script>

</body>

</html>
