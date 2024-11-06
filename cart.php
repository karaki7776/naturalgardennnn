<?php
session_start();








if (!isset($_SESSION['CustomerID'])) {
    header("Location: login-signin.php");
    exit();
}
include "config.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $userID = $_SESSION['CustomerID'];
    $productID = $_POST['product_id'];
    $quantity = 1;

    // التحقق من الكمية المتاحة للمنتج
    $sql = "SELECT StockQuantity FROM Products WHERE ProductID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $availableStock = $product['StockQuantity'];
    $stmt->close();

    // التحقق مما إذا كان المنتج موجودًا مسبقًا في السلة
    $sql = "SELECT * FROM Cart WHERE CustomerID = ? AND ProductID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userID, $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // جلب الكمية الحالية في السلة
        $row = $result->fetch_assoc();
        $currentQuantity = $row['Quantity'];
        $newQuantity = $currentQuantity + $quantity;

        // التحقق من الكمية المطلوبة مقابل المخزون المتاح
        if ($newQuantity > $availableStock) {
            $errorMessages[] = "The requested quantity is greater than the avaibale quantity";
        } else {
            // زيادة الكمية بمقدار 1
            $sql = "UPDATE Cart SET Quantity = ? WHERE CustomerID = ? AND ProductID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $newQuantity, $userID, $productID);
            $stmt->execute();
        }
    } else {
        // التحقق من الكمية المطلوبة مقابل المخزون المتاح
        if ($quantity > $availableStock) {
            $errorMessages[] = "The requested quantity is greater than the avaibale quantity";
        } else {
            // إضافة المنتج للسلة
            $sql = "INSERT INTO Cart (CustomerID, ProductID, Quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $userID, $productID, $quantity);
            $stmt->execute();
        }
    }

    $stmt->close();

    // توجيه المستخدم إلى صفحة السلة
    if (empty($errorMessages)) {
        header("Location: cart.php");
        exit();
    }
}

// تحديث الكميات عند الضغط على "Checkout Now"
if (isset($_POST['checkout'])) {
    $errorMessages = [];
    foreach ($_POST['quantities'] as $cartID => $quantity) {
        $quantity = intval($quantity);

        // استرجاع المخزون المتاح
        $sql = "SELECT Products.StockQuantity AS AvailableStock FROM Cart 
                JOIN Products ON Cart.ProductID = Products.ProductID 
                WHERE Cart.CartID = ? AND Cart.CustomerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cartID, $_SESSION['CustomerID']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $availableStock = $row['AvailableStock'];

        // التحقق من الكمية المتاحة
        if ($quantity > $availableStock) {
            $errorMessages[$cartID] = "The requested quantity is greater than the avaibale quantity " . $availableStock;
        } else {
            $sql = "UPDATE Cart SET Quantity = ? WHERE CartID = ? AND CustomerID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $cartID, $_SESSION['CustomerID']);
            $stmt->execute();
        }
        $stmt->close();
    }

    // توجيه المستخدم إلى صفحة checkout.php بعد التحديث
    if (empty($errorMessages)) {
        header("Location: checkout.php?totalAmount=" . $_POST['totalAmount']);
        exit();
    }
}

// التحقق من طلب إزالة المنتج
if (isset($_POST['remove']) && isset($_POST['cart_id'])) {
    $cartID = $_POST['cart_id'];
    $sql = "DELETE FROM Cart WHERE CartID = ? AND CustomerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cartID, $_SESSION['CustomerID']);
    $stmt->execute();
    $stmt->close();

    // توجيه المستخدم لتحديث الصفحة بعد الإزالة
    header("Location: cart.php");
    exit();
}

// استعلام لاسترجاع بيانات السلة
$sql = "SELECT Cart.CartID, Products.ProductName, Products.Price, Products.ImageURL, Cart.Quantity, Products.StockQuantity AS AvailableStock
        FROM Cart 
        JOIN Products ON Cart.ProductID = Products.ProductID 
        WHERE Cart.CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['CustomerID']);
$stmt->execute();
$result = $stmt->get_result();

// حساب الإجمالي
$totalPrice = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>
    <h2>Your Shopping Cart <i class="ri-shopping-cart-line"></i></h2>

    <!-- عرض محتويات السلة -->
    <form action="cart.php" method="post">
        <div class="cart-container">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $itemTotal = $row['Price'] * $row['Quantity'];
                    $totalPrice += $itemTotal;

                    echo "<div class='cart-item'>";
                    echo "<img src='" . $row['ImageURL'] . "' alt='" . $row['ProductName'] . "' class='product-image'>";
                    echo "<div class='product-details'>";
                 
                    echo "<h3>" . $row['ProductName'] . "</h3>";
                   
                    echo "<p>Price: $" . number_format($row['Price'], 2) . "</p>";
                    echo "<p>Quantity: <input type='number' name='quantities[" . $row['CartID'] . "]' value='" . $row['Quantity'] . "' min='1'></p>";
                    
                    if (isset($errorMessages[$row['CartID']])) {
                        echo "<h4 class='stock-error'>" . $errorMessages[$row['CartID']] . "</h4>";
                    }
                    

                    //remove 
                    echo "<form method='post' action='cart.php'>";
                    echo "<input type='hidden' name='cart_id' value='" . $row['CartID'] . "'>";
                    echo "<button type='submit' name='remove' class='remove-btn'>Remove</button>";
                    echo "</form>";
                    
                    echo "</div>";
                    echo "</div>";
                }

                echo "<div class='cart-summary'>";
                echo "<input type='hidden' name='totalAmount' value='" . $totalPrice . "'>";
                echo "<button type='submit' name='checkout' class='checkout-btn'>Checkout Now</button>";
                echo "</div>";
            } else {
                echo "<div class='cart-empty-container'>";
                echo "<img src='img/cart3.png' alt='Empty Cart'>";
                echo "<h1>Your cart is empty.</h1>";
                echo "<p>What are you waiting for ?</p>";
                echo "<a href='homepage.php' class='shop-now-btn'>Start shopping</a>";
                echo "</div>";
            }
            
            ?>
        </div>
    </form>

<?php $conn->close(); ?> 

</body>
</html>
