<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

require '../config/db.php';

$sql = "
    SELECT 
        t.id, t.name, t.slug, t.image, t.area, t.full_address, 
        t.length, t.width, t.google_map_link, t.opening_time, 
        t.closing_time, t.price_per_hour, t.upi, t.phone, t.amenities,
        t.created_at, t.updated_at,
        
        o.id AS owner_id, o.name AS owner_name, o.email AS owner_email, o.phone AS owner_phone
        
    FROM turfs t
    JOIN turfowner o ON t.turf_owner_id = o.id
    ORDER BY t.id DESC
";

$result = $conn->query($sql);
$response = [];

if ($result && $result->num_rows > 0) {
    $turfs = [];

    while ($row = $result->fetch_assoc()) {
        $turfs[] = [
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
    }

    $response['status'] = 'success';
    $response['data'] = $turfs;
} else {
    $response['status'] = 'error';
    $response['message'] = 'No turfs found.';
}

echo json_encode($response);
