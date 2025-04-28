<?php
header('Content-Type: application/json');
include('../db_connection.php');

$subservice_id = isset($_GET['subservice_id']) ? (int)$_GET['subservice_id'] : 0;

if ($subservice_id === 0) {
    echo json_encode(['error' => 'Invalid subservice ID']);
    exit();
}

try {
    $query = "
        SELECT st.id, st.name
        FROM staff st
        JOIN staff_subservices sts ON st.id = sts.staff_id
        WHERE sts.subservice_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $subservice_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $staff = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $staff[] = $row;
    }
    mysqli_stmt_close($stmt);
    echo json_encode($staff);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>