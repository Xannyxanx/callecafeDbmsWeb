<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$servername = "localhost";
$username = "root";
$password = "";
$archiveDb = "archived"; 

$conn = new mysqli($servername, $username, $password, $archiveDb);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: 0.login.html"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$branch = $_SESSION['branch'];

$date = isset($_GET['date']) ? $_GET['date'] : '';
$query = isset($_GET['query']) ? $_GET['query'] : '';
$selectedBranch = isset($_GET['branch']) ? strtolower(trim($_GET['branch'])) : 'all';

if (empty($date)) {
    echo json_encode([]); 
    exit();
}

// Fetch distinct branches from the users table, excluding "superadmin"
$branchConn = new mysqli($servername, $username, $password, "callecafe"); // Connect to the main database
if ($branchConn->connect_error) {
    die("Connection failed: " . $branchConn->connect_error);
}

$sqlBranches = "SELECT DISTINCT branch FROM users WHERE branch IS NOT NULL AND branch != '' AND LOWER(branch) != 'superadmin'";
$resultBranches = $branchConn->query($sqlBranches);

$branches = [];
if ($resultBranches->num_rows > 0) {
    while ($row = $resultBranches->fetch_assoc()) {
        $branches[] = $row['branch'];
    }
}
$branchConn->close();

// Check if the archive table for the selected date exists
$tableExistsQuery = "SHOW TABLES LIKE '$date'";
$tableExistsResult = $conn->query($tableExistsQuery);

if ($tableExistsResult->num_rows == 0) {
    echo json_encode(['branches' => $branches, 'records' => []]); // Include branches even if no records exist
    exit();
}

// Fetch records from the archive table
$sql = "SELECT ID, name, citizen, food, date, time, cashier, branch, discount_percentage, price, discounted_price, control_number FROM `$date`";

// Add branch filtering if a specific branch is selected
if ($selectedBranch !== 'all') {
    $sql .= " WHERE LOWER(branch) = '$selectedBranch'";
}

// Add search query filtering
if (!empty($query)) {
    $query = $conn->real_escape_string($query); 
    $sql .= ($selectedBranch === 'all' ? " WHERE " : " AND ") . "(
        ID LIKE '%$query%' OR 
        name LIKE '%$query%' OR 
        citizen LIKE '%$query%' OR 
        food LIKE '%$query%' OR 
        date LIKE '%$query%' OR 
        time LIKE '%$query%' OR 
        cashier LIKE '%$query%'
    )";
}

$result = $conn->query($sql);

$records = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

$conn->close();

// Include both branches and records in the JSON response
header('Content-Type: application/json');
echo json_encode([
    "branches" => $branches,
    "records" => $records
]);
?>