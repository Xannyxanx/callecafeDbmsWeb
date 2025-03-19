<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "callecafe";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: 0.login.html");
    exit();
}

$branch = isset($_GET['branch']) ? strtolower(trim($_GET['branch'])) : 'all';

// Build the SQL query based on the branch filter
if ($branch === 'dapitan') {
    $sql = "SELECT *, 'Dapitan' AS branch FROM dapitan_users";
} elseif ($branch === 'espana') {
    $sql = "SELECT *, 'Espana' AS branch FROM espana_users";
} else {
    // Fetch data from both tables if "all" is selected
    $sql = "SELECT *, 'Dapitan' AS branch FROM dapitan_users
            UNION
            SELECT *, 'Espana' AS branch FROM espana_users";
}

$result = $conn->query($sql);

$users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($users);
?>