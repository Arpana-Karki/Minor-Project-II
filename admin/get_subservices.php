<?php
header('Content-Type: application/json');

try {
    include '../db_connection.php';
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $parent_service_id = isset($_GET['parent_service_id']) ? intval($_GET['parent_service_id']) : 0;
    if ($parent_service_id <= 0) {
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, subservice_name FROM subservices WHERE parent_service_id = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $parent_service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subservices = [];
    while ($row = $result->fetch_assoc()) {
        $subservices[] = $row;
    }

    echo json_encode($subservices);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([]);
}
?>