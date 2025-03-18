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

if (empty($date)) {
    echo json_encode([]); 
    exit();
}

$tableExistsQuery = "SHOW TABLES LIKE '$date'";
$tableExistsResult = $conn->query($tableExistsQuery);

if ($tableExistsResult->num_rows == 0) {
    echo json_encode(['error' => 'Table does not exist for the selected date']);
    exit();
}

$sql = "SELECT ID, name, citizen, food, date, time, cashier, discount_percentage FROM `$date` WHERE branch = '$branch'";

if (!empty($query)) {
    $query = $conn->real_escape_string($query); 
    $sql .= " AND (
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
        if (!empty($row['customer_ID'])) {
            $row['customer_ID'] = base64_encode($row['customer_ID']);
        }
        $records[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($records);
?>