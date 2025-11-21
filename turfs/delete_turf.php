<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "POST request required"]);
    exit;
}

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Turf ID required"]);
    exit;
}

// Fetch image name before deleting
$imgQuery = $conn->prepare("SELECT image FROM turfs WHERE id = ?");
$imgQuery->bind_param("i", $id);
$imgQuery->execute();
$imgResult = $imgQuery->get_result();

if ($imgResult->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Turf not found"]);
    exit;
}

$row = $imgResult->fetch_assoc();
$imageName = $row['image'];

// Delete turf bookings first
// $conn->prepare("DELETE FROM bookings WHERE turf_id = ?")->bind_param("i", $id)->execute();

// Delete turf record
$stmt = $conn->prepare("DELETE FROM turfs WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    // Delete image file
    $imgPath = "../turfImages/" . $imageName;
    if (file_exists($imgPath)) {
        unlink($imgPath);
    }

    echo json_encode(["status" => "success", "message" => "Turf deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete turf"]);
}