<?php


include 'config.php';

// التحقق مما إذا كان هناك `product_id` تم إرساله
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // استعلام لجلب بيانات المنتج بناءً على `product_id`
    $sql = "SELECT * FROM Products WHERE ProductID = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // جلب البيانات وعرضها في الحقول
        $row = $result->fetch_assoc();
        $product_name = $row['ProductName'];
        $category_id = $row['CategoryID'];
        $price = $row['Price'];
        $stock_quantity = $row['StockQuantity'];
        $description = $row['Description'];
        $image_url = $row['ImageURL'];
    } else {
        echo "المنتج غير موجود";
        exit;
    }
} else {
    echo "لم يتم توفير معرف المنتج";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $description = $_POST['description'];
    
    // التحقق مما إذا تم تحميل صورة جديدة
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "categorie/"; 
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // التحقق من نوع الملف
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "الملف ليس صورة.";
            $uploadOk = 0;
        }

        // التحقق من حجم الملف
        if ($_FILES["image"]["size"] > 500000) {
            echo "عذراً، حجم الملف كبير جداً.";
            $uploadOk = 0;
        }

        // السماح بأنواع معينة من الملفات
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            echo "عذراً، فقط ملفات JPG, JPEG, PNG مسموح بها.";
            $uploadOk = 0;
        }

        // التحقق مما إذا كان $uploadOk يساوي 0
        if ($uploadOk == 0) {
            echo "عذراً، لم يتم رفع الملف.";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // استكمال التحديث في قاعدة البيانات بعد رفع الصورة بنجاح
                $image_url = $target_file;
            } else {
                echo "عذراً، حدث خطأ أثناء رفع الملف.";
            }
        }
    }

    // تحديث بيانات المنتج في قاعدة البيانات
    $sql = "UPDATE Products SET ProductName='$product_name', CategoryID='$category_id', Price='$price', StockQuantity='$stock_quantity', Description='$description', ImageURL='$image_url' WHERE ProductID=$product_id";

    if ($conn->query($sql) === TRUE) {
        echo "تم تحديث المنتج بنجاح";
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
   
<title>Edit Product</title>
<link rel="stylesheet " href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <!----favicom-->
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <!--remix icon-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
    rel="stylesheet">
<link rel="stylesheet" href="css/add-product.css">
</head>
<body>
<div class="logo">
            <i class="ri-leaf-line">Natural Garden</i>     
        </div>
    <h2>Edit Product</h2>
    <form class="container" method="post" action="edit.php?product_id=<?php echo $product_id; ?>" enctype="multipart/form-data">
        <label>Product Name:</label><br>
        <input type="text" name="product_name" value="<?php echo $product_name; ?>" required><br>
       <label>Categorie:</label><br>
    <select name="category_id">
        <?php
        include('config.php');
        $categories = mysqli_query($conn, "SELECT * FROM categories");
        while ($c = mysqli_fetch_array($categories)) {
            echo "<option value='{$c['CategoryID']}'>{$c['CategoryName']}</option>";
        }
        ?>
    </select><br>
        <label>Price:</label><br>
        <input type="text" name="price" value="<?php echo $price; ?>" required><br>
        <label>Stock Quantity:</label><br>
        <input type="number" name="stock_quantity" value="<?php echo $stock_quantity; ?>" required><br>
        <label>Description:</label><br>
        <textarea name="description" required><?php echo $description; ?></textarea><br>
        <label>Current Image:</label><br>
        <img src="<?php echo $image_url; ?>" alt="Product Image" style="max-width: 150px;"><br>
        <label>Upload New Image (if you want to change):</label><br>
        <input type="file" name="image"><br>
        <button type="submit">Update Product</button>
    </form>
</body>
</html>
