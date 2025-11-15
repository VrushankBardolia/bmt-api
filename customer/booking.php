<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require '../config/db.php';

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate fields
if (
    !isset($data['turf_id'], $data['email'], $data['date'], $data['start_time'],
    $data['end_time'], $data['duration'], $data['total_amount'], $data['advance_amount'])
) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$turf_id = $data['turf_id'];
$email = $data['email'];
$date = $data['date'];
$start_time = $data['start_time'];
$end_time = $data['end_time'];
$duration = $data['duration'];
$total_amount = $data['total_amount'];
$advance_amount = $data['advance_amount'];

// Step 1: Fetch user_id from email
$userQuery = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQuery->bind_param("s", $email);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found for the given email"]);
    exit;
}

$user = $userResult->fetch_assoc();
$user_id = $user['id'];

// Step 2: Insert booking
$stmt = $conn->prepare("
    INSERT INTO bookings 
    (turf_id, user_id, date, start_time, end_time, duration, total_amount, advance_amount, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
");
$stmt->bind_param(
    "iisssidd",
    $turf_id,
    $user_id,
    $date,
    $start_time,
    $end_time,
    $duration,
    $total_amount,
    $advance_amount
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Booking created successfully",
        "data" => [
            "booking_id" => $stmt->insert_id,
            "user_id" => $user_id,
            "email" => $email
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to create booking",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
