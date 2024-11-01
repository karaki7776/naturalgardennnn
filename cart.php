<?php
session_start();

// تحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login-signin.php");
    exit();
}

// إعدادات الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// التحقق من وجود product_id في الطلب وإضافته إلى السلة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $userID = $_SESSION['CustomerID'];
    $productID = $_POST['product_id'];
    $quantity = 1; // كمية افتراضية للمنتج عند إضافته لأول مرة

    // التحقق مما إذا كان المنتج موجودًا مسبقًا في السلة
    $sql = "SELECT * FROM Cart WHERE CustomerID = ? AND ProductID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userID, $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // إذا كان المنتج موجودًا، قم بزيادة الكمية بمقدار 1
        $sql = "UPDATE Cart SET Quantity = Quantity + ? WHERE CustomerID = ? AND ProductID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $userID, $productID);
    } else {
        // إذا كان المنتج غير موجود، قم بإضافته
        $sql = "INSERT INTO Cart (CustomerID, ProductID, Quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $userID, $productID, $quantity);
    }

    $stmt->execute();
    $stmt->close();

    // توجيه المستخدم إلى صفحة السلة لعرض المنتجات
    header("Location: cart.php");
    exit();
}

// استعلام لاسترجاع بيانات المنتجات في السلة (باقي الكود في صفحة cart.php)






// الحصول على معرف المستخدم من الجلسة
$userID = $_SESSION['CustomerID'];


// معالجة تحديث الكميات في سلة التسوق
if (isset($_POST['updateCart'])) {
    foreach ($_POST['quantities'] as $cartID => $quantity) {
        $quantity = intval($quantity);

        // استرجاع المخزون المتاح لهذا المنتج
        $sql = "SELECT Products.StockQuantity AS AvailableStock FROM Cart 
                JOIN Products ON Cart.ProductID = Products.ProductID 
                WHERE Cart.CartID = ? AND Cart.CustomerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cartID, $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $availableStock = $row['AvailableStock']; // الكمية المتاحة في المخزون

        // تحقق مما إذا كانت الكمية المطلوبة أكبر من المتاحة
        if ($quantity > $availableStock) {
            // إضافة رسالة خطأ إذا لم يكن المخزون كافيًا
            $errorMessages[$cartID] = "لا تتوفر الكمية الكافية. الكمية المتاحة: " . $availableStock;
        } else {
            // تحديث الكمية إذا كانت الكمية المطلوبة متاحة
            $sql = "UPDATE Cart SET Quantity = ? WHERE CartID = ? AND CustomerID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $cartID, $userID);
            $stmt->execute();
        }

        $stmt->close();
    }
}

// استعلام لاسترجاع بيانات المنتجات في سلة التسوق
$sql = "SELECT Cart.CartID, Products.ProductName, Products.Price, Products.ImageURL, Cart.Quantity, Products.StockQuantity AS AvailableStock
        FROM Cart 
        JOIN Products ON Cart.ProductID = Products.ProductID 
        WHERE Cart.CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// تعريف المتغيرات لحساب الإجمالي
$totalPrice = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/cart.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Favicon -->
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>Your Shopping Cart <i class="ri-shopping-cart-line"></i></h2>

    <!-- عرض محتويات سلة التسوق -->
    <form action="cart.php" method="post">
        <div class="cart-container">

<?php
if (isset($_POST['removeProduct'])) {
    // اجلب قيمة CartID من الطلب
    $cartID = $_POST['cart_id'];

    // قم بإنشاء استعلام SQL لحذف المنتج من قاعدة البيانات
    $sql = "DELETE FROM Cart WHERE CartID = ? AND CustomerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cartID, $_SESSION['CustomerID']);
    
    if ($stmt->execute()) {
        // إعادة التوجيه إلى صفحة السلة لتحديث القائمة بعد الحذف
        header("Location: cart.php");
        exit();
    }

    $stmt->close();
}

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $itemTotal = $row['Price'] * $row['Quantity']; // حساب إجمالي المنتج الواحد
                $totalPrice += $itemTotal; // إضافة سعر المنتج للإجمالي

                echo "<div class='cart-item'>";
                echo "<img src='" . $row['ImageURL'] . "' alt='" . $row['ProductName'] . "' class='product-image'>";
                echo "<div class='product-details'>";
                echo "<h3>" . $row['ProductName'] . "</h3>";
                echo "<p>Price: $" . number_format($row['Price'], 2) . "</p>";
                echo "<p>Quantity: <input type='number' name='quantities[" . $row['CartID'] . "]' value='" . $row['Quantity'] . "' min='1'></p>";

                // عرض رسالة خطأ إذا كانت الكمية المطلوبة أكبر من المتاحة
                if (isset($errorMessages[$row['CartID']])) {
                    echo "<p class='error'>" . $errorMessages[$row['CartID']] . "</p>";
                }

                // زر "Remove Product"
                echo "<form action='cart.php' method='post'>";
                echo "<input type='hidden' name='cart_id' value='" . $row['CartID'] . "'>";
                echo "<button type='submit' name='removeProduct' class='remove-btn'><i class='fa fa-trash'></i></button>";
                echo "</form>";

                echo "</div>";
                echo "</div>";
            }

            echo "<button type='submit' name='updateCart' class='update-btn'>Update Cart</button>";
            echo "<div class='cart-summary'>";
            echo "<form action='checkout.php' method='post'>";
            echo "<input type='hidden' name='totalAmount' value='" . $totalPrice . "'>";  // إرسال المجموع الكلي إلى صفحة الـ checkout
            echo "<button type='submit' class='checkout-btn'>Checkout Now</button>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<div class='cart-empty-container'>";
            echo "<img src='img/cart3.png' alt='Empty Cart'>";
            echo "<h1>Your cart is empty.</h1>";
            echo "<p>What are you waiting for ?</p>";
            echo "<a href='homepage.php' class='shop-now-btn'>Start shoping</a>";
            echo "</div>";
        }
        ?>
        </div>
    </form>

<?php $conn->close(); ?>

</body>
</html>
