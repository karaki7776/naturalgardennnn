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



// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// تأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['CustomerID'])) {
    echo "No user is logged in.";
    exit();
}

$userID = $_SESSION['CustomerID'];

// التحقق من أن orderID موجود في عنوان URL
if (!isset($_GET['OrderID']) || !is_numeric($_GET['OrderID'])) {
    echo "Invalid order ID.";
    exit();
}

$orderID = intval($_GET['OrderID']);

// جلب تفاصيل الطلب
$sqlOrder = "SELECT * FROM orders WHERE OrderID = ? AND customerID = ?";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("ii", $orderID, $userID);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();

if ($resultOrder->num_rows > 0) {
    $order = $resultOrder->fetch_assoc();
    $orderDate = $order['OrderDate'];
    $totalAmount = $order['TotalAmount'];
} else {
    echo "Order not found.";
    exit();
}

// جلب تفاصيل الطلبات الفردية
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
    <title>Order Details</title>
    <link rel="stylesheet" href="css/orders.css">
</head>
<body>
    <h2>Order Details</h2>
    <div class="order-summary">
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($orderID); ?></p>
        <p><strong>Date Added:</strong> <?php echo htmlspecialchars($orderDate); ?></p>
        <p><strong>Total:</strong> $<?php echo number_format($totalAmount, 2); ?></p>

        <h3>Products:</h3>
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
            <a href="orders.php">Back to Orders</a>
        </div>
    </div>
</body>
</html>
