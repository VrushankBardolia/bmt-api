<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require '../config/db.php';

// Get email from query or JSON body
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? null;
} else {
    $email = $_GET['email'] ?? null;
}

if (!$email) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

// Fetch user_id using email
$user_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
$user_query->bind_param("s", $email);
$user_query->execute();
$user_result = $user_query->get_result();

if ($user_result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$user = $user_result->fetch_assoc();
$user_id = $user['id'];

// Fetch all bookings for this user
$query = $conn->prepare("
    SELECT 
        b.id, b.date, b.start_time, b.end_time, b.duration, 
        b.total_amount, b.advance_amount, b.created_at,
        t.id AS turf_id, t.name AS turf_name, t.image AS turf_image, 
        t.area AS turf_area, t.price_per_hour, t.full_address
    FROM bookings b
    JOIN turfs t ON b.turf_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.date DESC, b.start_time ASC
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$bookings = [];

while ($row = $result->fetch_assoc()) {
    $bookings[] = [
        "booking_id" => $row['id'],
        "date" => $row['date'],
        "start_time" => $row['start_time'],
        "end_time" => $row['end_time'],
        "duration" => $row['duration'],
        "total_amount" => $row['total_amount'],
        "advance_amount" => $row['advance_amount'],
        "created_at" => $row['created_at'],
        "turf" => [
            "id" => $row['turf_id'],
            "name" => $row['turf_name'],
            "image" => $row['turf_image'],
            "area" => $row['turf_area'],
            "price_per_hour" => $row['price_per_hour']
        ]
    ];
}

if (count($bookings) > 0) {
    echo json_encode(["status" => "success", "bookings" => $bookings]);
} else {
    echo json_encode(["status" => "error", "message" => "No bookings found"]);
}