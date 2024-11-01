<?php
session_start();

// تحقق من تسجيل الدخول وامتلاك دور admin
if (!isset($_SESSION['CustomerID']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super admin')) {
    header("location:login-signin.php");
    exit();
}

// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// التحقق من وجود OrderID في عنوان URL
if (!isset($_GET['OrderID']) || !is_numeric($_GET['OrderID'])) {
    echo "Invalid order ID.";
    exit();
}

$orderID = intval($_GET['OrderID']);

// جلب تفاصيل الطلب
$sqlOrder = "SELECT o.OrderID, o.OrderDate, o.TotalAmount, u.username, u.phone
             FROM orders o
             JOIN users u ON o.customerID = u.CustomerID
             WHERE o.OrderID = ?";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("i", $orderID);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();

if ($resultOrder->num_rows > 0) {
    $order = $resultOrder->fetch_assoc();
    $orderDate = $order['OrderDate'];
    $totalAmount = $order['TotalAmount'];
    $customerName = $order['username'];
    $customerPhone = $order['phone'];
} else {
    echo "Order not found.";
    exit();
}

// جلب تفاصيل المنتجات في الطلب
$sqlOrderDetails = "SELECT p.ProductName, od.Quantity, od.Price
                    FROM ordersdetail od
                    JOIN products p ON od.ProductID = p.ProductID
                    WHERE od.OrderID = ?";
$stmtOrderDetails = $conn->prepare($sqlOrderDetails);
$stmtOrderDetails->bind_param("i", $orderID);
$stmtOrderDetails->execute();
$resultOrderDetails = $stmtOrderDetails->get_result();

$orderDetails = [];
while ($row = $resultOrderDetails->fetch_assoc()) {
    $orderDetails[] = $row;
}

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Order Details</title>
    <link rel="stylesheet" href="css/orders.css">
</head>
<body>
    <h2>Order Details - Admin View</h2>
    <div class="order-summary">
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($orderID); ?></p>
        <p><strong>Date Added:</strong> <?php echo htmlspecialchars($orderDate); ?></p>
        <p><strong>Total Amount:</strong> $<?php echo number_format($totalAmount, 2); ?></p>
        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($customerName); ?></p>
        <p><strong>Customer Phone:</strong> <?php echo htmlspecialchars($customerPhone); ?></p>

        <h3>Products in Order:</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderDetails as $detail): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['ProductName']); ?></td>
                        <td><?php echo htmlspecialchars($detail['Quantity']); ?></td>
                        <td>$<?php echo number_format($detail['Price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- رابط للعودة إلى قائمة الطلبات -->
        <div class="back-link">
            <a href="admin-orders.php">Back to Orders</a>
        </div>
    </div>
</body>
</html>
