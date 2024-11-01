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



$userID = $_SESSION['CustomerID'];

// جلب معلومات المستخدم
$sql = "SELECT * FROM users WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profilePicture = isset($user['profile_picture']) ? $user['profile_picture'] : 'default-profile.png';
    $username = isset($user['username']) ? $user['username'] : 'Unknown User';
    $email = isset($user['Email']) ? $user['Email'] : 'No Email';
    $phone = isset($user['phone']) ? $user['phone'] : 'No Phone';
    $address = isset($user['Address']) ? $user['Address'] : 'No Address';
} else {
    echo "User not found.";
    exit();
}

// جلب الطلبات الخاصة بالمستخدم بدون عمود الحالة
$sqlOrders = "SELECT o.OrderID, o.orderDate, o.totalAmount 
               FROM orders o 
               WHERE o.customerID = ?";
$stmtOrders = $conn->prepare($sqlOrders);
$stmtOrders->bind_param("i", $userID);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();

// إذا كان هناك طلبات
$orders = [];
if ($resultOrders->num_rows > 0) {
    while ($row = $resultOrders->fetch_assoc()) {
        $orders[] = $row;
    }
} else {
    echo "No orders found.";
}

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="css/orders.css">
</head>
<body>
    <h2>Orders</h2>

 
   
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date Added</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <?php
                    // حساب الأيام منذ تاريخ الطلب
                    $orderDate = new DateTime($order['orderDate']);
                    $currentDate = new DateTime();
                    $interval = $currentDate->diff($orderDate);
                    $daysPassed = $interval->days;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['OrderID']); ?></td>
                    <td><?php echo htmlspecialchars($order['orderDate']); ?></td>
                    <td>$<?php echo number_format($order['totalAmount'], 2); ?></td>
                    <td>
                        <?php if ($daysPassed < 2): ?>
                            <a href="view-order.php?OrderID=<?php echo htmlspecialchars($order['OrderID']); ?>">View</a> |
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
