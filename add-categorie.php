<?php
// بدء الجلسة
session_start();

// إعدادات الاتصال بقاعدة البيانات
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "naturalgarden"; 

// الاتصال بقاعدة البيانات
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryName = $_POST['categoryName'];  
    $pageLink = $_POST['pageLink'];  
    $image = $_FILES['image']['name'];  
    $target = "categorie/" . basename($image);  

    // استعلام إدخال الفئة الجديدة في قاعدة البيانات
    $sql = "INSERT INTO categories (CategoryName, Image, PageLink) VALUES ('$categoryName', '$image', '$pageLink')";

    if ($conn->query($sql) === TRUE) {
        // التحقق من تحميل الصورة بنجاح إلى المسار المحدد
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            echo "New category added successfully.";
        } else {
            echo "Failed to upload image.";
        }
        header("Location: categorie.php");  
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link rel="stylesheet" href="css/categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
</head>
<body>
    <section class="add-category-section">
        <h1>Add New Category</h1>
        <!-- نموذج لإضافة فئة جديدة -->
        <form action="add-categorie.php" method="post" enctype="multipart/form-data">
            <label for="categoryName">Category Name:</label>
            <input type="text" id="categoryName" name="categoryName" required>

            <label for="image">Category Image:</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <label for="pageLink">Category Page Link:</label>
            <input type="text" id="pageLink" name="pageLink" placeholder="Enter the page URL" required>

            <button type="submit">Add Category</button>
        </form>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container-footer">
            <p>Contact us:</p>
            <ul class="social-links">
                <!-- روابط وسائل التواصل الاجتماعي -->
            </ul>
            <p>&copy; 2024 Natural Garden. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
