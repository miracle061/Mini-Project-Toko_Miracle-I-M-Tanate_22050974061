<?php
include '../src/config.php';

// Try a simple query
$result = $conn->query("SHOW TABLES");

if ($result) {
    echo "<h3>Database connected successfully!</h3>";
    echo "<p>Here are your tables:</p>";

    while ($row = $result->fetch_array()) {
        echo $row[0] . "<br>";
    }
} else {
    echo "<p>Query failed: " . $conn->error . "</p>";
}

$conn->close();
?>
