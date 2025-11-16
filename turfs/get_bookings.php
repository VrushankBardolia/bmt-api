<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require '../config/db.php';

// Get email from POST or GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? null;
} else {
    $email = $_GET['email'] ?? null;
}

if (!$email) {
    echo json_encode(["status" => "error", "message" => "Owner email is required"]);
    exit;
}

// 1️⃣ Fetch owner ID
$owner_query = $conn->prepare("SELECT id FROM turfowner WHERE email = ?");
$owner_query->bind_param("s", $email);
$owner_query->execute();
$owner_result = $owner_query->get_result();

if ($owner_result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Owner not found"]);
    exit;
}

$owner = $owner_result->fetch_assoc();
$owner_id = $owner['id'];


// 2️⃣ Fetch bookings for ALL turfs owned by this owner
$query = $conn->prepare("
    SELECT 
        b.id AS booking_id,
        b.date, b.start_time, b.end_time, b.duration,
        b.total_amount, b.advance_amount, b.created_at,

        t.id AS turf_id,
        t.name AS turf_name,
        t.full_address AS turf_address,
        t.phone AS turf_phone,

        u.id AS user_id,
        u.name AS user_name,
        u.email AS user_email,
        u.phone AS user_phone

    FROM bookings b
    INNER JOIN turfs t ON b.turf_id = t.id
    INNER JOIN turfowner u ON b.user_id = u.id
    WHERE t.turf_owner_id = ?
    ORDER BY b.date DESC, b.start_time ASC
");

$query->bind_param("i", $owner_id);
$query->execute();
$result = $query->get_result();

$bookings = [];

while ($row = $result->fetch_assoc()) {
    $bookings[] = [
        "booking_id" => $row['booking_id'],
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
            "address" => $row['turf_address'],
            "phone" => $row['turf_phone'],
        ],

        "customer" => [
            "id" => $row['user_id'],
            "name" => $row['user_name'],
            "email" => $row['user_email'],
            "phone" => $row['user_phone'],
        ]
    ];
}

if (!empty($bookings)) {
    echo json_encode(["status" => "success", "bookings" => $bookings]);
} else {
    echo json_encode(["status" => "error", "message" => "No bookings found"]);
}