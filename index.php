<?php
header("Content-Type: application/json");

echo json_encode([
    "status" => "success",
    "message" => "Turf Booking API is running successfully."
]);
