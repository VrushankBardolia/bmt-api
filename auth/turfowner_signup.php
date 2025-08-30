<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include '../config/db.php';

$data = json_decode(file_get_contents("php://input"));
$fullname = $data->name ?? '';
$email = $data->email ?? '';
$phone = $data->phone ?? '';
$password = $data->password ?? '';


if (!$fullname || !$email || !$phone || !$password) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

$check = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
$check->bind_param("ss", $email, $phone);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email or phone already exists."]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO turfowner (name, email, phone, password, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $fullname, $email, $phone, $hashedPassword);

if ($stmt->execute()) {
    // echo json_encode(["status" => "success", "message" => "Customer registered successfully."]);
    $turfowner_id = $stmt->insert_id;

    echo json_encode([
        "status" => "success",
        "message" => "Turfowner registered successfully.",
        "id" => $turfowner_id
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed."]);
}