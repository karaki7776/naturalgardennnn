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



?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <title>Natural Garden </title>
    <link rel="stylesheet" href="css/styles.css">
    <!--font awesome for icons------->
    <link rel="stylesheet " href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <!----favicom-->
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <!--remix icon-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
    rel="stylesheet">
</head>
<body>
<header class="header" >
    <div class="container">
        <div class="logo">
            <i class="ri-leaf-line">Natural Garden</i>     
        </div>
        <nav class="navbar">
            <div class="menu-icon">
                <img src="img/333.png" alt="">
            </div> 
            <ul>
                <li><a href="homepage.php">Home</a></li>
               <?php   
               if($admin==false){   
                echo"<li><a href='about.php'>About</a></li>";
                echo"<li><a href='contact-us.php'>Contact us</a></li>";
               }
                ?>
               <li><a href="categorie.php">Categorie</a></li>
               
            </ul>
        </nav>
       
      <div class="icons">  
       

    <a href="profile.php">
        <button class="profile-btn">
            <i class="ri-user-line"></i>
        </button>
    </a>
    <?php
    if($admin==false){   
        echo"<a href='cart.php'>
        <button class='cart-btn'>
            <i class='ri-shopping-cart-line'></i>
        </button>
    </a>";
               }
                ?>
    
</div>
    </div>
    <div class="hero-content" style="   background-image: url('img/backg1.jpg');
    background-size: cover;
    background-position: center;">
        <div class="text-content">
            <h2> PLANTS  WILL  MAKE<br> YOUR  LIFE   BETTER  </h2>
            <p>We offer a wide range of high-quality<br>
                agricultural equipment and trees.</p>
            
<?php
if($admin==false){
                echo"<div class='buttons'>
                <a href='about.php' class='btn primary-btn'>Explore<i class='ri-arrow-right-down-line'></i></a>
                </div>";
                }
            ?>   
        </div>
        <div class="image-content">
            <img src="img/homepageimgg.png" alt="Natural Garden">
        </div>
    </div>
</header>
    <main>
    <?php
// الاتصال بقاعدة البيانات
include 'config.php';
if (isset($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    
    // استعلام البحث
    $sql = "SELECT * FROM Products WHERE ProductName LIKE '%$searchTerm%' OR Description LIKE '%$searchTerm%'";
    $result = $conn->query($sql);
} else {
    // جلب جميع المنتجات بشكل افتراضي
    $sql = "SELECT * FROM Products ORDER BY RAND() LIMIT 24";
    $result = $conn->query($sql);
}

?>

    <section class="products">
    <div class="our-product">
    <h2>Our Products</h2>
    </div>

    
        <div class="search-bar-container">
            <form action="homepage.php" method="get">
                <input type="text" name="search" placeholder="Search for products...">
                <input type="submit" value="Search">
            </form>
        </div>
        <?php
if ($result->num_rows > 0) {
    echo '<div class="product-grid">';

    while ($row = $result->fetch_assoc()) {
        echo '<div class="product-item">';
        echo '<img src="' . $row["ImageURL"] . '" alt="' . $row["ProductName"] . '">';
        echo '<h3>' . $row["ProductName"] . '</h3>';
        echo '<p>' . $row["Description"] . '</p>';
        echo '<h4>$' . $row["Price"] . '</h4>';

        // تحقق من كمية المنتج
        if ($row['StockQuantity'] > 0) {
            // إذا كانت الكمية أكثر من 0، يعرض زر Add to Cart
if($admin== false){      
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

        if ($admin == true) {
            echo "<div class='div-edit'>";
            echo "<a href='edit.php?product_id=" . $row['ProductID'] . "'><i class='fa fa-pencil'></i>Edit</a>";
            echo "<a href='delete.php?product_id=" . $row['ProductID'] . "' onclick=\"return confirm('هل أنت متأكد من حذف هذا المنتج؟');\"><i class='fa fa-trash'></i></a>";
            echo "</div>";
        }

        echo '</div>';
    }

    echo '</div>';
    echo '</section>';
} else {
    echo '<div class="pp">No product available</div>';
}

$conn->close();
?>

<?php 
if ($admin == true) { 
 echo'   <div class="add-product-card">
        <div class="add-product">
            <a href="add-product.php"><i class="fas fa-plus"> Add product</i></a>
        </div>
    </div>';
}
?>
</div>
<div>
<a href="categorie.php" class="view-all">View All Products</a>
</div>
</section>

</main>
<!-------------scroll reveal animation-------------------->
<script src="js/scrollreveal.min.js"></script>
 <!---========js==================-->
 <script src="addtocart.js"></script>
</body>
<?php include 'footer.php' ; ?>

</html>


