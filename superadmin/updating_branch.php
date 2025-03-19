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
$branch = isset($data['branch']) ? strtolower(trim($data['branch'])) : null;
$name = isset($data['name']) ? trim($data['name']) : null;
$email = isset($data['email']) ? trim($data['email']) : null;
$new_password = isset($data['password']) ? trim($data['password']) : null;
$confirm_password = isset($data['confirmPassword']) ? trim($data['confirmPassword']) : null;

if (empty($branch)) {
    die(json_encode(["success" => false, "error" => "No branch selected."]));
}

// Prepare updates and parameters
$updates = [];
$params = [];
$types = "";

if (!empty($name)) {
    $updates[] = "name = ?";
    $params[] = $name;
    $types .= "s";
}

if (!empty($email)) {
    $updates[] = "email = ?";
    $params[] = $email;
    $types .= "s";
}

if (!empty($new_password)) {
    if ($new_password !== $confirm_password) {
        die(json_encode(["success" => false, "error" => "Passwords do not match."]));
    }
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $updates[] = "password = ?";
    $params[] = $hashed_password;
    $types .= "s";
}

if (empty($updates)) {
    die(json_encode(["success" => false, "error" => "No fields to update."]));
}

// Add the branch to the parameters
$params[] = $branch;
$types .= "s";

// Build the query
$query = "UPDATE users SET " . implode(", ", $updates) . " WHERE LOWER(branch) = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die(json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]));
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Branch account updated successfully."]);
} else {
    echo json_encode(["success" => false, "error" => "Update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>