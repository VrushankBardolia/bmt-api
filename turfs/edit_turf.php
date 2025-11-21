<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require '../config/db.php';

$response = [];

// Only allow POST multipart
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "POST request required"]);
    exit;
}

// Fetch POST data from multipart request
$id              = $_POST['id'] ?? null;
$name            = $_POST['name'] ?? null;
$area            = $_POST['area'] ?? null;
$full_address    = $_POST['full_address'] ?? null;
$map_link        = $_POST['google_map_link'] ?? null;
$price_per_hour  = $_POST['price_per_hour'] ?? null;
$length          = $_POST['length'] ?? null;
$width           = $_POST['width'] ?? null;
$phone           = $_POST['phone'] ?? null;
$amenities       = isset($_POST['amenities']) ? json_decode($_POST['amenities'], true) : [];
$opening_time    = $_POST['opening_time'] ?? null;
$closing_time    = $_POST['closing_time'] ?? null;
$old_image       = $_POST['old_image'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Turf ID required"]);
    exit;
}

$amenitiesJson = json_encode($amenities);

// -------------------------------
// ✅ IMAGE UPLOAD HANDLING
// -------------------------------
$newImageName = $old_image; // default keep old image

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $uploadDir = "../turfImages/";
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $newImageName = "turf_" . time() . "." . $ext;

    move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImageName);

    // Delete old image
    if (!empty($old_image) && file_exists($uploadDir . $old_image)) {
        unlink($uploadDir . $old_image);
    }
}

// -------------------------------
// ✅ SQL Update Query
// -------------------------------
$stmt = $conn->prepare("
    UPDATE turfs SET 
        name = ?, 
        area = ?, 
        full_address = ?, 
        google_map_link = ?, 
        price_per_hour = ?,
        length = ?, 
        width = ?, 
        phone = ?, 
        amenities = ?, 
        opening_time = ?, 
        closing_time = ?, 
        image = ?, 
        updated_at = NOW()
    WHERE id = ?
");

$stmt->bind_param(
    "ssssiiisssssi",
    $name,
    $area,
    $full_address,
    $map_link,
    $price_per_hour,
    $length,
    $width,
    $phone,
    $amenitiesJson,
    $opening_time,
    $closing_time,
    $newImageName, // updated or old image name
    $id
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Turf updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update turf"]);
}
?>
