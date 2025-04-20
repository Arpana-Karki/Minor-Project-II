<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}
include('../db_connection.php');

// Handle cancellation
if (isset($_GET['cancel'])) {
    $cancel_id = intval($_GET['cancel']);
    mysqli_query($conn, "UPDATE bookings SET status = 'Cancelled' WHERE id = $cancel_id");
    header("Location: booking.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | View Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.8s ease;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #4a69bd;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .back:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        th {
            background-color: #4a69bd;
            color: white;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        .btn-cancel {
            background-color: #e74c3c;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-cancel:hover {
            background-color: #c0392b;
        }

        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
        }

        .pending {
            background-color: #f6c23e;
            color: #fff;
        }

        .cancelled {
            background-color: #e74c3c;
            color: #fff;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="back" href="dash.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <h2>All Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Staff</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Landmark</th>
                    <th>Email</th>
                    <th>Note</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM bookings ORDER BY booking_date DESC, booking_time DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                    $statusClass = $row['status'] === 'Cancelled' ? 'cancelled' : 'pending';
                    echo "<tr>
                        <td>{$row['service']}</td>
                        <td>{$row['staff_name']}</td>
                        <td>{$row['customer_name']}</td>
                        <td>{$row['address']}</td>
                        <td>{$row['phone']}</td>
                        <td>{$row['landmark']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['order_note']}</td>
                        <td>{$row['booking_date']}</td>
                        <td>{$row['booking_time']}</td>
                        <td><span class='status $statusClass'>{$row['status']}</span></td>
                        <td>";
                        if ($row['status'] !== 'Cancelled') {
                            echo "<a href='booking.php?cancel={$row['id']}' onclick='return confirm(\"Are you sure you want to cancel this booking?\");'>
                                    <button class='btn-cancel'>Cancel</button>
                                  </a>";
                        } else {
                            echo "-";
                        }
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
