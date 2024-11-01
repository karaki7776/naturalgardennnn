<?php
include "config.php";









// التحقق من وجود `CategoryID` في الرابط
if (isset($_GET['CategoryID'])) {
    $Category_id = intval($_GET['CategoryID']); // تأكد من تحويل القيمة إلى عدد صحيح

    // حذف الفئة المحددة من قاعدة البيانات
    $sql = "DELETE FROM Categories WHERE CategoryID = $Category_id";

    if ($conn->query($sql) === TRUE) {
        // إعادة التوجيه إلى الصفحة الرئيسية بعد الحذف
        header("Location: categorie.php");
        exit();
    } else {
        echo "خطأ: " . $conn->error;
    }
} else {
    echo "لم يتم توفير معرف الفئة.";
    exit();
}

$conn->close();
?>
