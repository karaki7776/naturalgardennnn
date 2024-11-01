<?php
session_start();

if (!isset($_SESSION['CustomerID'])) {
    header("location:login-signin.php");
}

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super admin') {
        $admin = true;
    } else {
        $admin = false;
    }
} else {
    $admin = false; 
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// تعديل الاستعلام لربط جدول الطلبات مع جدول المستخدمين وجلب اسم المستخدم ورقم الهاتف
$sqlOrders = "SELECT o.OrderID, o.customerID, o.orderDate, o.totalAmount, 
                     u.username, u.phone 
              FROM orders o 
              JOIN users u ON o.customerID = u.CustomerID";
$resultOrders = $conn->query($sqlOrders);

$orders = [];
if ($resultOrders->num_rows > 0) {
    while ($row = $resultOrders->fetch_assoc()) {
        $orders[] = $row;
    }
} else {
    echo "No orders found.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders</title>
    <link rel="stylesheet" href="css/orders.css">
</head>
<body>
    <h2>All Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Order ID</th>
                <th>Date Added</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <?php
                    $orderDate = new DateTime($order['orderDate']);
                    $currentDate = new DateTime();
                    $interval = $currentDate->diff($orderDate);
                    $daysPassed = $interval->days;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['customerID']); ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td><?php echo htmlspecialchars($order['phone']); ?></td>
                    <td><?php echo htmlspecialchars($order['OrderID']); ?></td>
                    <td><?php echo htmlspecialchars($order['orderDate']); ?></td>
                    <td>$<?php echo number_format($order['totalAmount'], 2); ?></td>
                    <td>
                        <?php if ($daysPassed < 2): ?>
                            <a href="view-order-admin.php?OrderID=<?php echo htmlspecialchars($order['OrderID']); ?>">View</a>

                            <a href="delete-order.php?OrderID=<?php echo htmlspecialchars($order['OrderID']); ?>" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                        <?php else: ?>
                            تم تجهيز طلبك
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
