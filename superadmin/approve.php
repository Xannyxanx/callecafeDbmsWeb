<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$servername = "localhost";
$username = "root";
$password = "";
$customersDb = "callecafe";

$conn = new mysqli($servername, $username, $password, $customersDb);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

if (!isset($_SESSION['branch'])) {
    error_log("Session branch not set");
    echo json_encode(["status" => "error", "message" => "Session branch is missing."]);
    exit();
}

$branch = $_SESSION['branch'];

error_log("Received POST data: " . print_r($_POST, true));

if (!isset($_POST['id']) || empty($_POST['id'])) {
    error_log("No request ID received: " . print_r($_POST, true));
    echo json_encode(["status" => "error", "message" => "No request ID provided"]);
    exit();
}

$requestID = intval($_POST['id']);
error_log("Approving Request ID: " . $requestID);


if ($branch == "Dapitan") {
    $checkUserQuery = "SELECT * FROM `dapitan_users_request` WHERE ID = $requestID";
} else {
    $checkUserQuery = "SELECT * FROM `espana_users_request` WHERE ID = $requestID";
}

$result = $conn->query($checkUserQuery);
if ($result->num_rows == 0) {
    error_log("User with ID $requestID not found in requests table.");
    echo json_encode(["status" => "error", "message" => "User not found."]);
    exit();
}


if ($branch == "Dapitan") {
    $sqlTransferData = "INSERT INTO `dapitan_users` (ID, name, username, pin) SELECT ID, name, username, pin FROM `dapitan_users_request` WHERE ID = ?";
    $sqlDelete = "DELETE FROM `dapitan_users_request` WHERE ID = ?";
} else {
    $sqlTransferData = "INSERT INTO `espana_users` (ID, name, username, pin) SELECT ID, name, username, pin FROM `espana_users_request` WHERE ID = ?";
    $sqlDelete = "DELETE FROM `espana_users_request` WHERE ID = ?";
}

$stmt = $conn->prepare($sqlTransferData);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "SQL Prepare failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $requestID);
if (!$stmt->execute()) {
    error_log("SQL Error (Insert): " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    exit();
}

$stmt = $conn->prepare($sqlDelete);
$stmt->bind_param("i", $requestID);

if (!$stmt->execute()) {
    error_log("SQL Error (Delete): " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Error deleting request: " . $stmt->error]);
    exit();
}

echo json_encode(["status" => "success", "message" => "Request approved successfully."]);

$stmt->close();
$conn->close();
?>