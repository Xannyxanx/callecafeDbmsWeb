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

if (!isset($_POST['id']) || empty($_POST['id']) || !isset($_POST['pin']) || empty($_POST['pin']) || !isset($_POST['username']) || empty($_POST['username'])) {
    error_log("No user ID, PIN, or username received: " . print_r($_POST, true));
    echo json_encode(["status" => "error", "message" => "No user ID, PIN, or username provided"]);
    exit();
}

$userID = intval($_POST['id']);
$newPin = $_POST['pin'];
$newUsername = $_POST['username'];
error_log("Updating user ID: " . $userID . " with new PIN: " . $newPin . " and new username: " . $newUsername);

if ($branch == "Dapitan") {
    $sqlUpdate = "UPDATE `dapitan_users` SET pin = ?, username = ? WHERE ID = ?";
} else {
    $sqlUpdate = "UPDATE `espana_users` SET pin = ?, username = ? WHERE ID = ?";
}

$stmt = $conn->prepare($sqlUpdate);
$stmt->bind_param("ssi", $newPin, $newUsername, $userID);

if (!$stmt->execute()) {
    error_log("SQL Error (Update): " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Error updating user: " . $stmt->error]);
    exit();
}

$response = ["status" => "success", "message" => "User PIN and username have been updated"];
error_log("Response: " . json_encode($response));

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>