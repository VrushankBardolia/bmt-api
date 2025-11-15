<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require '../config/db.php';

$turf_id = $_GET['turf_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$turf_id || !$date) {
    echo json_encode(["status" => "error", "message" => "Missing turf_id or date"]);
    exit;
}

// Fetch turf timings
$turf_query = $conn->prepare("SELECT opening_time, closing_time FROM turfs WHERE id = ?");
$turf_query->bind_param("i", $turf_id);
$turf_query->execute();
$turf_result = $turf_query->get_result()->fetch_assoc();

$opening_time = $turf_result['opening_time'];
$closing_time = $turf_result['closing_time'];

// Handle midnight (00:00:00) as next-day 24:00:00
$end_time_obj = new DateTime($closing_time);
if ($closing_time === "00:00:00") {
    $end_time_obj->modify('+1 day');
}

$slots = [];
$current = new DateTime($opening_time);

while ($current < $end_time_obj) {
    $slot_start = $current->format("H:i:s");
    $next = clone $current;
    $next->modify('+1 hour');
    $slot_end = $next->format("H:i:s");

    // Fetch bookings
    $check = $conn->prepare("
        SELECT COUNT(*) AS count FROM bookings 
        WHERE turf_id = ? 
          AND date = ? 
          AND (
              (start_time < ? AND end_time > ?) 
              OR (start_time = ? AND end_time = ?)
          )
    ");
    $check->bind_param("isssss", $turf_id, $date, $slot_end, $slot_start, $slot_start, $slot_end);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    $is_booked = $result['count'] > 0;

    $slots[] = [
        "start_time" => $slot_start,
        "end_time" => $slot_end,
        "is_booked" => $is_booked
    ];

    $current = $next;
}

echo json_encode(["status" => "success", "slots" => $slots]);