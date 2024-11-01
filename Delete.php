<?php
include "config.php";

// التحقق من وجود `product_id` في الرابط
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // حذف المنتج المحدد من قاعدة البيانات
    $sql = "DELETE FROM Products WHERE ProductID = $product_id";

    if ($conn->query($sql) === TRUE) {
        echo "تم حذف المنتج بنجاح";
        header("Location: homepage.php");
        exit();
    } else {
        echo "خطأ: " . $conn->error;
    }
} else {
    echo "لم يتم توفير معرف المنتج.";
    exit;
}

$conn->close();
?>
