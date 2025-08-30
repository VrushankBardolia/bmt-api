<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include '../config/db.php';

$response = [];

// Function to generate slug
function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // Remove invalid chars
    $slug = preg_replace('/[\s-]+/', '-', $slug); // Replace spaces & multiple hyphens
    return $slug;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turf_owner_id = $_POST['turf_owner_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $slug = generateSlug($name); // Generate slug from name
    $area = $_POST['area'] ?? '';
    $full_address = $_POST['full_address'] ?? '';
    $length = $_POST['length'] ?? '';
    $width = $_POST['width'] ?? '';
    $google_map_link = $_POST['google_map_link'] ?? '';
    $opening_time = $_POST['opening_time'] ?? '';
    $closing_time = $_POST['closing_time'] ?? '';
    $price_per_hour = $_POST['price_per_hour'] ?? '';
    $upi = $_POST['upi'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $amenities = $_POST['amenities'] ?? '[]'; // Stored as JSON string

    // Handle image upload
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = "turfImages/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        // $imageName = time() . "_" . basename($_FILES['image']['name']);
        $imageName = $slug . "." . strtolower($extension);
        $targetFile = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
            exit;
        }
    }

    // Insert into MySQL
    $stmt = $conn->prepare("INSERT INTO turfs 
        (turf_owner_id, name, slug, image, area, full_address, length, width, google_map_link, opening_time, closing_time, price_per_hour, upi, phone, amenities) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "isssssiiisssiss",
        $turf_owner_id, $name, $slug, $imageName, $area, $full_address, $length, $width, $google_map_link,
        $opening_time, $closing_time, $price_per_hour, $upi, $phone, $amenities
    );

    if ($stmt->execute()) {
        $response = [
            "status" => "success",
            "message" => "Turf added successfully",
            "image_url" => "http://localhost/bmt-api/uploads/turfs/" . $imageName,
            "slug" => $slug
        ];
    } else {
        $response = ["status" => "error", "message" => "DB insert failed: " . $stmt->error];
    }
} else {
    $response = ["status" => "error", "message" => "Invalid request method"];
}

echo json_encode($response);
