<?php
session_start();
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

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$branch = $_SESSION['branch'];

$sql = "SELECT ID, name, citizen, food, date, time, cashier, discount_percentage
        FROM dapitancustomers
        WHERE branch = '$branch' 
        ORDER BY time DESC";
$result = $conn->query($sql);

$customers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($customers);
?>