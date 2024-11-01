<?php

session_start();





if(!isset($_SESSION['CustomerID']))
{
header("location:login-signin.php");
}

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'admin'  || $_SESSION['role'] == 'super admin') {
        $admin = true;
    } else {
        $admin = false;
    }
} else {
  
    

}


include 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <link rel="stylesheet" href="css/categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body   >
    
    <section class="categories" style="   background-image: url('img/backg1.jpg');
    background-size: cover;
    background-position: center;">
        <h1>Categories</h1>
        <p>Explore our wide range of agricultural categories.</p>
        <div class="categories-grid">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="category-card">';
                    echo '    <div class="category-image">';
                    
                    echo '<a href="' . $row['PageLink'] . '?categoryID=' . $row['CategoryID'] . '"><img src="categorie/' . $row['Image'] . '" alt="' . $row['CategoryName'] . '"></a>';
                    echo '    </div>';
                    echo '    <h2>' . $row['CategoryName'] . '</h2>';
                
                    if($admin == true){  
                        echo '<div class="div-edit">';
                        echo "<a href='edit-categorie.php?CategoryID=" . $row['CategoryID'] . "'><i class='fa fa-pencil'></i>Edit</a>";
                        echo "<a href='delete-categorie.php?CategoryID=" . $row['CategoryID'] . "' onclick=\"return confirm('هل أنت متأكد من حذف هذا المنتج؟');\"><i class='fa fa-trash'></i>Delete</a>";
                        echo '</div>';
                    }
                
                    echo '</div>';
                }
                
            } else {
                echo '<p>No categories found.</p>';
            }
            ?>
            <!-- مربع لإضافة فئة جديدة -->
            <div class="category-card">
         <?php   if($admin == true) {
   echo"         <div class='category-image add-category'>
                    <a href='add-categorie.php'><i class='fas fa-plus'></i></a>
                </div>
                <h2>Add Category</h2>
            </div>";
              } 
 ?>      
            </div>

    </section>

    <!-- Footer -->
    <?php include 'footer.php' ; ?>
</body>
</html>

<?php
$conn->close();
?>