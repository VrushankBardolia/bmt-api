<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require '../config/db.php';

$response = array();

// Validate parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Owner ID is required"
    ]);
    exit;
}

$ownerId = intval($_GET['id']);

try {
    // Fetch turfs owned by this owner
    $stmt = $conn->prepare("SELECT * FROM turfs WHERE turf_owner_id = ?");
    $stmt->bind_param("i", $ownerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $turfs = [];
    while ($row = $result->fetch_assoc()) {
        $row['amenities'] = isset($row['amenities']) ? json_decode($row['amenities'], true) : [];
        $turfs[] = $row;
    }

    if (count($turfs) > 0) {
        $response['status'] = "success";
        $response['data'] = $turfs;
    } else {
        $response['status'] = "empty";
        $response['data'] = "No turfs found";
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
