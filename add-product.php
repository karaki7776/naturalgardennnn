<?php




$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category-id'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $description = $_POST['description'];

    $target_dir = "categorie/"; 
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
   

   
  

   
       
          
            $sql = "INSERT INTO Products (ProductName, CategoryID, Price, StockQuantity, Description, ImageURL)
                    VALUES ('$product_name', '$category_id', '$price', '$stock_quantity', '$description', '$target_file')";

            if ($conn->query($sql) === TRUE) {
                echo "تمت إضافة المنتج بنجاح";
                header("location:categorie.php");
            } else {
                echo "خطأ: " . $sql . "<br>" . $conn->error;
            }
        } 
    


$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet"  href="css/add-product.css">
    <link rel="stylesheet " href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <!----favicom-->
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <!--remix icon-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
    rel="stylesheet">
 
 
    <title>Add New Product</title>
</head>

<body>
  <div class="logo">
            <i class="ri-leaf-line">Natural Garden</i>     
        </div>
    <h2>Add New Product</h2>
   
    <form class="container" method="post" action="add-product.php" enctype="multipart/form-data">
    <label>Product Name:</label><br>
    <input type="text" name="product_name" required><br>
    
    <label>Categorie:</label><br>
    <select name="category-id">
        <?php
        include('config.php');
        $categories = mysqli_query($conn, "SELECT * FROM categories");
        while ($c = mysqli_fetch_array($categories)) {
            echo "<option value='{$c['CategoryID']}'>{$c['CategoryName']}</option>";
        }
        ?>
    </select><br>
    
    <label>Price:</label><br>
    <input type="text" name="price" required><br>
    
    <label>Stock Quantity:</label><br>
    <input type="number" name="stock_quantity" required><br>
    
    <label>Description:</label><br>
    <textarea name="description" required></textarea><br>
    
    <label>Image:</label><br>
    <input type="file" name="image" required><br>
    
    <button type="submit">Add Product</button>
</form>
</body>
</html>
