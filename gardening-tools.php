<?php
session_start();

if (!isset($_SESSION['CustomerID'])) {
    header("location:login-signin.php");
    exit();
}


if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'admin'  || $_SESSION['role'] == 'super admin') {
        $admin = true;
    } else {
        $admin = false;
    }
} else {
  
    

}

// الاتصال بقاعدة البيانات
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gardening tools</title>
    <link rel="stylesheet" href="css/product.css">
    <!--font awesome for icons-->
    <link rel="stylesheet " href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!--favicon-->
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <!--remix icon-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<?php $categoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 1;?>
<section class="products">
    <div class="our-product">
        <h2>Gardening tools</h2>
    </div>
    
  
    <!-- شريط البحث -->
    <div class="search-bar-container">
        <form method="GET" action="flower.php">
            <input type="text" name="search" placeholder="Search for your product" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- مكان لعرض المنتجات -->
    <div id="product-grid" class="product-grid">
        <?php
        // الحصول على categoryID
      
       
        // البحث عن المنتجات
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchTerm = $conn->real_escape_string($_GET['search']);
            $sql = "SELECT * FROM Products WHERE categoryID = $categoryID AND (ProductName LIKE '%$searchTerm%' OR Description LIKE '%$searchTerm%')";
        } else {
            $sql = "SELECT * FROM Products WHERE categoryID = $categoryID";
        }

        // تنفيذ الاستعلام
        $result = $conn->query($sql);

        // عرض النتائج
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="product-item">';
                echo '<img src="' . $row["ImageURL"] . '" alt="' . $row["ProductName"] . '">';
                echo '<h3>' . $row["ProductName"] . '</h3>';
                echo '<p>' . $row["Description"] . '</p>';
                echo '<h4>$' . $row["Price"] . '</h4>';
        
                // تحقق من كمية المنتج
                if ($row['StockQuantity'] > 0) {
                    // إذا كانت الكمية أكثر من 0، يعرض زر Add to Cart
              if($admin==false){
                    echo "<div class='div-add-to-cart'><form action='cart.php' method='post'>";
                    echo "<input type='hidden' name='product_id' value='" . $row['ProductID'] . "'>";
                    echo "<input type='submit' value='Add to Cart'>";
                    echo "</form> </div>";
              }
                } else {
                    echo '<p style=" color: red;
                    font-weight: bold;
                    font-size: 20px;
                    margin-top: 10px;">
                    Out of Stock</p>';
                }
        
                if ($admin) {
                    echo "<div class='div-edit'>";
                    echo "<a href='edit.php?product_id=" . $row['ProductID'] . "'><i class='fa fa-pencil'></i>Edit</a>";
                    echo "<a href='delete.php?product_id=" . $row['ProductID'] . "' onclick=\"return confirm('هل أنت متأكد من حذف هذا المنتج؟');\"><i class='fa fa-trash'></i></a>";
                    echo "</div>";
                }
        
                echo '</div>';
            }
        } else {
            echo '<p>No products found</p>';
        }

        $conn->close();
        ?>
    </div>
</section>
<?php
if ($admin == true) { 
    echo'   <div class="add-product-card">
           <div class="add-product">
               <a href="add-product.php"><i class="fas fa-plus"> Add product</i></a>
           </div>
       </div>';
   }
   ?>
<!-- Footer -->
<?php include 'footer.php'; ?>
</body>
</html>
