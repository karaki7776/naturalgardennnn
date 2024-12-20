<?php
session_start();
include "config.php"; // ملف الاتصال بقاعدة البيانات

// تحقق من تسجيل الدخول
if (!isset($_SESSION['CustomerID'])) {
    header("location:login-signin.php");
    exit();
}

$customerID = $_SESSION['CustomerID'];

// الحصول على تفاصيل المستخدم
$sql = "SELECT * FROM users WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'] ?? 'Unknown User';
    $email = $user['Email'] ?? 'No Email';
    $address = $user['Address'] ?? 'No Address';
    $phone = isset($user['phone']) ? $user['phone'] : 'No Phone';
} else {
    echo "User not found.";
    exit();
}

// الحصول على تفاصيل السلة
$sqlCart = "SELECT p.ProductName, c.Quantity, p.Price, p.ProductID 
            FROM cart c 
            JOIN products p ON c.ProductID = p.ProductID 
            WHERE c.CustomerID = ?";
$stmtCart = $conn->prepare($sqlCart);
$stmtCart->bind_param("i", $customerID);
$stmtCart->execute();
$resultCart = $stmtCart->get_result();

$totalAmount = 0;
$products = [];

if ($resultCart->num_rows > 0) {
    while ($row = $resultCart->fetch_assoc()) {
        $products[] = $row;
        $totalAmount += $row['Quantity'] * $row['Price'];
    }
} else {
    echo "Your cart is empty.";
    exit();
}

// إضافة رسوم الشحن
$deliveryCharge = 5.00;
$totalAmountWithDelivery = $totalAmount + $deliveryCharge;

// إذا تم إرسال الطلب
$orderPlaced = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // إدخال الطلب في جدول الطلبات
    $sqlOrder = "INSERT INTO orders (customerID, orderDate, totalAmount) VALUES (?, NOW(), ?)";
    $stmtOrder = $conn->prepare($sqlOrder);
    $stmtOrder->bind_param("id", $customerID, $totalAmountWithDelivery);

    if ($stmtOrder->execute()) {
        $orderID = $conn->insert_id;

        // إدخال تفاصيل الطلب في جدول تفاصيل الطلبات
        $sqlOrderDetails = "INSERT INTO ordersdetail (OrderID, ProductID, Quantity, Price) VALUES (?, ?, ?, ?)";
        $stmtOrderDetails = $conn->prepare($sqlOrderDetails);

        foreach ($products as $product) {
            $stmtOrderDetails->bind_param(
                "iiid",
                $orderID,
                $product['ProductID'],
                $product['Quantity'],
                $product['Price']
            );
            $stmtOrderDetails->execute();
        }

        // حذف المنتجات من السلة
        $sqlClearCart = "DELETE FROM cart WHERE CustomerID = ?";
        $stmtClearCart = $conn->prepare($sqlClearCart);
        $stmtClearCart->bind_param("i", $customerID);
        $stmtClearCart->execute();

        $orderPlaced = true;
    } else {
        echo "Error placing order.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        function redirectToHomePage() {
            setTimeout(() => {
                window.location.href = 'homepage.php';
            }, 2000);
        }
    </script>
</head>
<body>
<a href="cart.php" class="back-arrow">
    <i class="fas fa-arrow-left"></i>
</a>
<h2>Checkout</h2>
<div class="checkout-summary">
    <?php if ($orderPlaced): ?>
        <p class="success-message" id="success-message">Your order has been placed successfully!</p>
        <script>
            // Call the redirection function when order is placed
            redirectToHomePage();
        </script>
    <?php else: ?>
        <p>Username: <?php echo htmlspecialchars($username); ?></p>
        <p>Email: <?php echo htmlspecialchars($email); ?></p>
        <p>Phone: 
    <?php 
        if ($phone === 'No Phone') {
            echo '<a href="edit-profile.php" style="color: red;">Please add your phone number</a>';
        } else {
            echo htmlspecialchars($phone);
        }
    ?>
</p>

<p>Address: 
    <?php 
        if ($address === 'No Address') {
            echo '<a href="edit-profile.php" style="color: red;">Please add your address</a>';
        } else {
            echo htmlspecialchars($address);
        }
    ?>
</p>
        <h3>Products Ordered:</h3>
        <ul>
        <?php foreach ($products as $product): ?>
            <li>
                <?php echo htmlspecialchars($product['ProductName']); ?> - 
                Quantity: <?php echo htmlspecialchars($product['Quantity']); ?> - 
                Price: $<?php echo number_format($product['Price'], 2); ?>
            </li>
        <?php endforeach; ?>
        </ul>

        <p>Total Amount: $<?php echo number_format($totalAmount, 2); ?></p>
        <p>Delivery Charge: $<?php echo number_format($deliveryCharge, 2); ?></p>
        <p><strong>Total with Delivery: $<?php echo number_format($totalAmountWithDelivery, 2); ?></strong></p>
  <!-- خيارات الدفع -->
  <h3>Payment Methods:</h3>
        <div class="payment-methods">
            <label class="payment-option">
                <input type="radio" name="paymentMethod" value="visa" id="visaOption">
                <img src="img/visa1.jfif" alt="Visa" class="payment-icon"> Visa
            </label>

            <!-- قسم تفاصيل الفيزا -->
            <div id="visaDetails" class="visa-details" style="display: none;">
                <label for="cardNumber">Card Number:</label>
                <input type="text" id="cardNumber" name="cardNumber" placeholder="Enter your card number">

                <label for="expiryDate">Expiry Date:</label>
                <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY">

                <label for="cvv">CVV:</label>
                <input type="text" id="cvv" name="cvv" placeholder="CVV">
            </div>

           <!-- PayPal Payment Option -->
           <label class="payment-option">
                <input type="radio" name="paymentMethod" value="paypal" id="paypalOption">
                <img src="img/paypal.jfif" alt="PayPal" class="payment-icon"> PayPal
            </label>

            <!-- قسم تفاصيل PayPal -->
            <div id="paypalDetails" class="paypal-details" style="display: none;">
                <p>To complete your purchase, you'll be redirected to PayPal's secure website.</p>
            </div>
            <label class="payment-option">
                <input type="radio" name="paymentMethod" value="paypal">
                <img src="img/cash-on-delivery.png" alt="PayPal" class="payment-icon"> Cash on delivery
            </label>
        </div>

    
 <!-- إضافة JavaScript هنا -->
 <script>
        document.addEventListener('DOMContentLoaded', function () {
            const visaOption = document.getElementById('visaOption');
            const paypalOption = document.getElementById('paypalOption');
            const visaDetails = document.getElementById('visaDetails');
            const paypalDetails = document.getElementById('paypalDetails');

            // عرض تفاصيل الفيزا عند اختيارها
            visaOption.addEventListener('change', function () {
                if (visaOption.checked) {
                    visaDetails.style.display = 'block';
                    paypalDetails.style.display = 'none';
                }
            });

            // عرض تفاصيل PayPal عند اختيارها
            paypalOption.addEventListener('change', function () {
                if (paypalOption.checked) {
                    paypalDetails.style.display = 'block';
                    visaDetails.style.display = 'none';
                }
            });

            // إخفاء تفاصيل كل الطرق عند اختيار طريقة دفع أخرى
            const paymentMethods = document.getElementsByName('paymentMethod');
            paymentMethods.forEach(function (method) {
                method.addEventListener('change', function () {
                    if (method.value !== 'visa' && method.value !== 'paypal') {
                        visaDetails.style.display = 'none';
                        paypalDetails.style.display = 'none';
                    }
                });
            });
        });
    </script>





    
        <!-- زر لإتمام الطلب -->
        <form method="post">
            <button type="submit">Place Order</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
