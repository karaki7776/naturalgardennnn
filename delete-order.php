<?php


session_start();

if(!isset($_SESSION['CustomerID']))
{
header("location:login-signin.php");
}

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'admin') {
        $admin = true;
    } else {
        $admin = false;
    }
} else {
  
    

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

// تأكد من وجود معرف الطلب في عنوان URL
if (!isset($_GET['OrderID']) || !is_numeric($_GET['OrderID'])) {
    echo "Invalid order ID.";
    exit();
}

$orderID = intval($_GET['OrderID']);

// جلب تفاصيل الطلب للتأكد من أن الطلب موجود وأن المستخدم لديه الصلاحيات لحذفه
$sql = "SELECT * FROM orders WHERE OrderID = ? AND CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orderID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Order not found or you do not have permission to delete this order.";
    exit();
}

// حذف تفاصيل الطلب من orderdetails
$sqlOrderDetails = "DELETE FROM ordersdetail WHERE OrderID = ?";
$stmtOrderDetails = $conn->prepare($sqlOrderDetails);
$stmtOrderDetails->bind_param("i", $orderID);
$stmtOrderDetails->execute();

// حذف الطلب من orders
$sqlOrder = "DELETE FROM orders WHERE OrderID = ?";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("i", $orderID);

if ($stmtOrder->execute()) {
    header("Location: orders.php"); // إعادة التوجيه إلى صفحة الطلبات بعد الحذف
    exit();
} else {
    echo "Error deleting order.";
}

$conn->close();
?>
