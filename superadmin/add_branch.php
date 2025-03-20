<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "callecafe";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Database connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die(json_encode(["success" => false, "error" => "Invalid request method."]));
}

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["success" => false, "error" => "User not logged in."]));
}

// Get the input data
$data = json_decode(file_get_contents("php://input"), true);
$branchName = isset($data['branchName']) ? trim($data['branchName']) : null;
$name = isset($data['name']) ? trim($data['name']) : null;
$email = isset($data['email']) ? trim($data['email']) : null;
$password = isset($data['password']) ? trim($data['password']) : null;

if (empty($branchName) || empty($name) || empty($email) || empty($password)) {
    die(json_encode(["success" => false, "error" => "All fields are required."]));
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the new branch and user
$sql = "INSERT INTO users (name, email, password, branch) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]));
}

$stmt->bind_param("ssss", $name, $email, $hashedPassword, $branchName);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "New branch added successfully."]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to add new branch: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>