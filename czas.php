<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Top ⏰ Czas</title>
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
            color: goldenrod;
            margin: 20px 15px;
            font-weight: bold;
            font-size: 36px;
        }

        table {
            margin-left: auto;
            margin-right: auto;
            width: 33%;
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

        .pagination a {
            text-decoration: none;
            color: #fff;
            font-size: 18px;
            margin: 0 8px;
        }

        .page-number {
            margin: 10px 5px; 
            font-size: 28px;
            font-weight: bold;
        }

        button {
            font-weight: bold;
            background-color: #444;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        button:hover {
            background-color: #555;
        }

        #search-input, .page-number {
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
</head>

<body>
    <h1 class="supreme">Top Czasu Gry</h1>

    <div id="search-container">
    <input type="text" id="search-input" placeholder="Wpisz swój nick..">
    <button id="search-button">Szukaj</button>
    </div>

    <div id="search-results"></div>

    <?php
    function formatTime($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
    
        if ($hours < 1) {
            return sprintf("%1dmin", $minutes);
        } else {
            return sprintf("%1dh:%1dm", $hours, $minutes);
        }
    }
    

    $showTable = true; // Set this variable to control table visibility

    // Check if there's a search query, and hide the table if needed
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $showTable = false;
    }
    ?>

    <script>
        function formatJSTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);

            const hoursString = hours < 10 ? `0${hours}` : `${hours}`;
            const minutesString = minutes < 10 ? `0${minutes}` : `${minutes}`;

            return `${hoursString}:${minutesString}`;
        }

        document.getElementById("search-input").addEventListener("input", function () {
            var searchQuery = this.value;
            var resultsContainer = document.getElementById("search-results");

            if (searchQuery.length >= 3) { // Adjust this threshold as needed
                // Make an asynchronous request to the search.php script
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "search.php?search=" + searchQuery, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var results = JSON.parse(xhr.responseText);
                        displaySearchResults(results);
                    }
                };
                xhr.send();
            } else {
                // Clear the results if the search query is too short
                resultsContainer.innerHTML = "";
            }
        });

        // Function to display search results
        function displaySearchResults(results) {
            var resultsContainer = document.getElementById("search-results");
            resultsContainer.innerHTML = "";

            if (results.length > 0) {
                var table = '<table><tr><th>Rank</th><th>Nick</th><th>Czas gry</th></tr>';
                let formattedTime;
                for (var i = 0; i < results.length; i++) {
                    formattedTime = formatJSTime(results[i]["Time Played"])
                    table += '<tr><td>' + (i + 1) + '</td><td>' + results[i]["Nick"] + '</td><td>' + formattedTime + '</td></tr>';
                }
                table += '</table>';
                resultsContainer.innerHTML = table;
            } else {
                resultsContainer.innerHTML = "No matching records found.";
            }
        }
    </script>

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

    // Pagination settings
    $results_per_page = 15;
    $current_page = 1;

    if (isset($_GET['page'])) {
        $current_page = $_GET['page'];
    }

    $start_from = ($current_page - 1) * $results_per_page;
    ?>

    <?php
    // Only display the table if $showTable is true
    if ($showTable) {
        $sql = "SELECT `Nick`, `Time Played` FROM TimePlayed WHERE `Time Played` >= 60 ORDER BY `Time Played` DESC LIMIT $start_from, $results_per_page";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>Rank</th><th>Nick</th><th>Czas gry</th></tr>';
            $rank = $start_from + 1;
    
            while ($row = $result->fetch_assoc()) {
                $formattedTime = formatTime($row["Time Played"]);
    
                // Apply colors to the top three players only
                $colorClass = '';
                if ($rank == 1) {
                    $colorClass = 'gold';
                } elseif ($rank == 2) {
                    $colorClass = 'silver';
                } elseif ($rank == 3) {
                    $colorClass = 'bronze';
                }
    
                // Output the row with the color class applied
                echo '<tr><td>' . $rank . '</td><td><span class="' . $colorClass . '">' . $row["Nick"] . '</span></td><td>' . $formattedTime . '</td></tr>';
                $rank++;
            }
    
            echo '</table>';
    
            // Calculate the total number of pages
            $sql = "SELECT COUNT(*) as total FROM TimePlayed WHERE `Time Played` >= 60"; // Apply the filter
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $total_pages = ceil($row['total'] / $results_per_page);
        } else {
            echo "No records found";
        }
    }
    ?>

    <?php
    // Close the database connection
    $conn->close();
    ?>

    <div class="pagination">
    <div id="search-container">
        <input type="number" id="page-number" name="page-number" min="1" max="<?php echo $total_pages; ?>" value="<?php echo $current_page; ?>">
        <button id="go-to-page" onclick="goToPage()">Idź</button>
    </div>
        
        <!-- Pagination links -->
        <?php
        if ($total_pages > 1) {
            if ($current_page > 1) {
                echo '<a href="?page=' . ($current_page - 1) . '">&lt;</a>'; // Link to the previous page
            }
            
            // Show the current page
            echo '<span class="current-page">' . $current_page . '</span>';

            if ($current_page < $total_pages) {
                echo '<a href="?page=' . ($current_page + 1) . '">&gt;</a>'; // Link to the next page
            }

            if ($current_page > 2) {
                echo '<a href="?page=1">1</a>'; // Link to the first page
            }
            
            if ($current_page > 3) {
                echo '<span>...</span>'; // Display "..." if there are in-between pages
            }

            for ($page = max(2, $current_page - 1); $page <= min($total_pages - 1, $current_page + 1); $page++) {
                if ($page != 1 && $page != $total_pages) {
                    if ($page != $current_page) {
                        echo '<a href="?page=' . $page . '">' . $page . '</a>';
                    }
                }
            }

            if ($current_page < $total_pages - 2) {
                echo '<span>...</span>'; // Display "..." if there are in-between pages
            }

            if ($current_page < $total_pages - 1) {
                echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>'; // Link to the last page
            }
        }
        ?>
    </div>
    
    <a href="../statmenu.html"><button>Statystyki</button></a>
    <a href="../index.html"><button>Menu główne</button></a>
    <iframe src="../footer.html" scrolling="no" frameborder="0" width="100%" height="160"></iframe>

    <script>
        // JavaScript function to go to the selected page
        function goToPage() {
            var page = document.getElementById("page-number").value;
            window.location.href = "?page=" + page;
        }
    </script>
</body>
</html>
