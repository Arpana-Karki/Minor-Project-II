<?php
header('Content-Type: application/json');
session_start();

// Include database connection
try {
    include('../db_connection.php');
} catch (Exception $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

if ($package_id <= 0) {
    echo json_encode(['error' => 'Invalid package ID']);
    exit;
}

// Check if package_services table exists
$check_table_query = "SHOW TABLES LIKE 'package_services'";
$check_table_result = mysqli_query($conn, $check_table_query);
if (mysqli_num_rows($check_table_result) == 0) {
    echo json_encode(['error' => 'Table package_services does not exist']);
    exit;
}

// Fetch services and their available staff
try {
    $query = "
        SELECT s.id AS service_id, s.service_name,
               GROUP_CONCAT(st.id) AS staff_ids,
               GROUP_CONCAT(st.name SEPARATOR ', ') AS staff_names
        FROM package_services ps
        JOIN services s ON ps.service_id = s.id
        LEFT JOIN staff st ON st.service_category = s.service_name
        WHERE ps.package_id = ?
        GROUP BY s.id";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $package_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $services = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $staff_ids = $row['staff_ids'] ? explode(',', $row['staff_ids']) : [];
        $staff_names = $row['staff_names'] ? explode(', ', $row['staff_names']) : [];
        $staff = [];
        for ($i = 0; $i < count($staff_ids); $i++) {
            $staff[] = ['id' => (int)$staff_ids[$i], 'name' => $staff_names[$i]];
        }
        $services[] = [
            'service_id' => $row['service_id'],
            'service_name' => $row['service_name'],
            'staff' => $staff
        ];
    }
    mysqli_stmt_close($stmt);
    
    echo json_encode(['services' => $services]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}
exit;