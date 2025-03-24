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

// Fetch distinct branches from the users table, excluding "superadmin"
$sqlBranches = "SELECT DISTINCT branch_name FROM branches WHERE branch_name IS NOT NULL AND branch_name != '' AND LOWER(branch_name) != 'superadmin'";
$resultBranches = $conn->query($sqlBranches);

$branches = [];
if ($resultBranches->num_rows > 0) {
    while ($row = $resultBranches->fetch_assoc()) {
        $branches[] = $row['branch_name'];
    }
}

// Fetch customers based on the selected branch
$selectedBranch = isset($_GET['branch']) ? strtolower(trim($_GET['branch'])) : 'all';

if ($selectedBranch === "all") {
    $sql = "SELECT ID, name, citizen, food, date, time, cashier, branch, discount_percentage, price, discounted_price, control_number
            FROM dapitancustomers
            ORDER BY time DESC";
} else {
    $sql = "SELECT ID, name, citizen, food, date, time, cashier, branch, discount_percentage, price, discounted_price, control_number
            FROM dapitancustomers
            WHERE LOWER(branch) = '$selectedBranch'
            ORDER BY time DESC";
}

$result = $conn->query($sql);

$customers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

$conn->close();

// Include both branches and customers in the JSON response
header('Content-Type: application/json');
echo json_encode([
    "branches" => $branches,
    "customers" => $customers
]);
?>