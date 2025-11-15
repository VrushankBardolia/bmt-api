<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

require '../config/db.php';

// Check if turf ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Turf ID is required"
    ]);
    exit;
}

$turfId = intval($_GET['id']);

$sql = "
    SELECT 
        t.id, t.name, t.slug, t.image, t.area, t.full_address, 
        t.length, t.width, t.google_map_link, t.opening_time, 
        t.closing_time, t.price_per_hour, t.upi, t.phone, t.amenities,
        t.created_at, t.updated_at,

        o.id AS owner_id, o.name AS owner_name, o.email AS owner_email, o.phone AS owner_phone
        
    FROM turfs t
    JOIN turfowner o ON t.turf_owner_id = o.id
    WHERE t.id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $turfId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $turf = [
        'id' => $row['id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'image' => $row['image'],
        'area' => $row['area'],
        'full_address' => $row['full_address'],
        'length' => $row['length'],
        'width' => $row['width'],
        'map_link' => $row['google_map_link'],
        'opening_time' => $row['opening_time'],
        'closing_time' => $row['closing_time'],
        'price_per_hour' => $row['price_per_hour'],
        'upi' => $row['upi'],
        'phone' => $row['phone'],
        'amenities' => json_decode($row['amenities'], true),
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
        'owner' => [
            'id' => $row['owner_id'],
            'name' => $row['owner_name'],
            'email' => $row['owner_email'],
            'phone' => $row['owner_phone'],
        ]
    ];

    echo json_encode([
        "status" => "success",
        "data" => $turf
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Turf not found"
    ]);
}