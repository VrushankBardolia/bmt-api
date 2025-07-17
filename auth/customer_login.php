<?php
header('Content-Type: application/json');
include '../config/db.php';

$data = json_decode(file_get_contents("php://input"));

$email = $data->email ?? '';
$password = $data->password ?? '';

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Email and password are required."]);
    exit;
}

$stmt = $conn->prepare("SELECT id, fullname, email, phone, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        unset($row['password']);
        echo json_encode(["status" => "success", "message" => "Login successful", "data" => $row]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not found."]);
}