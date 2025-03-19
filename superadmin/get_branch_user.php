<?php
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "callecafe";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Fetch all available branches
$sqlBranches = "SELECT DISTINCT branch FROM users WHERE branch IS NOT NULL AND branch != ''";
$resultBranches = $conn->query($sqlBranches);

$branches = [];
if ($resultBranches->num_rows > 0) {
    while ($row = $resultBranches->fetch_assoc()) {
        $branches[] = $row['branch'];
    }
}

// Fetch user details for the selected branch
$branch = isset($_GET['branch']) ? strtolower(trim($_GET['branch'])) : '';

if (empty($branch)) {
    // Return all branches if no branch is selected
    echo json_encode(["success" => true, "branches" => $branches]);
    exit();
}

$sql = "SELECT name, email FROM users WHERE LOWER(branch) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $branch);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(["success" => true, "user" => $user, "branches" => $branches]);
} else {
    echo json_encode(["success" => false, "error" => "No user found for the selected branch.", "branches" => $branches]);
}

$stmt->close();
$conn->close();
?>