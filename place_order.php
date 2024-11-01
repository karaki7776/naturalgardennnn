<?php
session_start();

if (!isset($_SESSION['CustomerID'])) {
    header("location:login-signin.php");
}

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        $admin = true;
    } else {
        $admin = false;
    }
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['CustomerID'])) {
    echo "No user is logged in.";
    exit();
}

$customerID = $_SESSION['CustomerID'];
$totalAmountWithDelivery = $_POST['totalAmountWithDelivery'];



$sqlOrder = "INSERT INTO orders (customerID, orderDate, totalAmount) VALUES (?, NOW(), ?)";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("id", $customerID, $totalAmountWithDelivery);

if ($stmtOrder->execute()) {
    $orderID = $conn->insert_id;

    
    $sqlCart = "SELECT p.ProductID, c.Quantity, p.Price, p.StockQuantity 
                FROM cart c 
                JOIN products p ON c.ProductID = p.ProductID 
                WHERE c.CustomerID = ?";
    $stmtCart = $conn->prepare($sqlCart);
    $stmtCart->bind_param("i", $customerID);
    $stmtCart->execute();
    $resultCart = $stmtCart->get_result();


    $sqlOrderDetails = "INSERT INTO ordersdetail (OrderID, ProductID, Quantity, Price) VALUES (?, ?, ?, ?)";
    $stmtOrderDetails = $conn->prepare($sqlOrderDetails);


    $sqlUpdateStock = "UPDATE products SET StockQuantity = StockQuantity - ? WHERE ProductID = ?";

    while ($row = $resultCart->fetch_assoc()) {
        $stmtOrderDetails->bind_param("iiid", $orderID, $row['ProductID'], $row['Quantity'], $row['Price']);
        $stmtOrderDetails->execute();

        
        if ($row['StockQuantity'] >= $row['Quantity']) {
            $stmtUpdateStock = $conn->prepare($sqlUpdateStock);
            $stmtUpdateStock->bind_param("ii", $row['Quantity'], $row['ProductID']);
            $stmtUpdateStock->execute();
        } else {
            
            echo "<p>Insufficient stock for Product ID " . $row['ProductID'] . ". Order cannot be completed.</p>";
            $conn->rollback(); 
            exit();
        }
    }

    
    $sqlClearCart = "DELETE FROM cart WHERE CustomerID = ?";
    $stmtClearCart = $conn->prepare($sqlClearCart);
    $stmtClearCart->bind_param("i", $customerID);
    $stmtClearCart->execute();

    echo "<p>Order placed successfully</p>.";
    header("refresh:2;url=homepage.php");
} else {
    echo "Error placing order.";
}

$conn->close();
?>
