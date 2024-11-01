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

// التحقق من وجود معرف الفئة
if (!isset($_GET['CategoryID'])) {
    die("Category ID is required.");
}

$categoryId = $_GET['CategoryID'];

// استعلام لاسترجاع تفاصيل الفئة
$sql = "SELECT * FROM categories WHERE CategoryID = $categoryId";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Category not found.");
}

$category = $result->fetch_assoc();

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryName = $_POST['categoryName'];  
    $pageLink = $_POST['pageLink'];  
    $image = $_FILES['image']['name'];  
    $target = "categorie/" . basename($image);

    // استعلام لتحديث الفئة
    if ($image) {
        // إذا تم تحميل صورة جديدة
        $sql = "UPDATE categories SET CategoryName='$categoryName', Image='$image', PageLink='$pageLink' WHERE CategoryID=$categoryId";
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            echo "Category updated successfully.";
        } else {
            echo "Failed to upload image.";
        }
    } else {
        // إذا لم يتم تحميل صورة جديدة
        $sql = "UPDATE categories SET CategoryName='$categoryName', PageLink='$pageLink' WHERE CategoryID=$categoryId";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: categorie.php");  // إعادة توجيه المستخدم إلى صفحة الفئات
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
    <title>Edit Category</title>
    <link rel="stylesheet" href="css/categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
</head>
<body>
    <section class="add-category-section">
        <h1>Edit Category</h1>
        <!-- نموذج لتعديل الفئة -->
        <form action="edit-categorie.php?CategoryID=<?php echo $categoryId; ?>" method="post" enctype="multipart/form-data">
            <label for="categoryName">Category Name:</label>
            <input type="text" id="categoryName" name="categoryName" value="<?php echo htmlspecialchars($category['CategoryName']); ?>" required>

            <label for="image">Category Image:</label>
            <input type="file" id="image" name="image" accept="image/*">

            <label for="pageLink">Category Page Link:</label>
            <input type="text" id="pageLink" name="pageLink" value="<?php echo htmlspecialchars($category['PageLink']); ?>" required>

            <button type="submit">Update Category</button>
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
