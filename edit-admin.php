<?php
session_start();
include 'config.php'; // تأكد من تضمين ملف الاتصال بقاعدة البيانات

// التحقق من أن المستخدم هو super admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super admin') {
    header("Location: login-superadmin.php");
    exit();
}

// التحقق من وجود معرف المدير للتعديل
if (isset($_POST['admin_id'])) {
    $admin_id = $_POST['admin_id'];
    
    // جلب بيانات المدير الحالية من قاعدة البيانات
    $query = "SELECT * FROM users WHERE CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin) {
        // إذا لم يتم العثور على المدير، إعادة توجيه إلى الصفحة الرئيسية للمديرين
        header("Location: admin_management.php");
        exit();
    }
} else {
    header("Location: admin_management.php");
    exit();
}

// تحديث معلومات المدير
if (isset($_POST['update_admin'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // تحديث معلومات المدير في قاعدة البيانات
    $update_query = "UPDATE users SET username = ?, Email = ?, Phone = ? WHERE CustomerID = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $username, $email, $phone, $admin_id);
    $update_stmt->execute();
    
    $success_message = "تم تحديث بيانات المدير بنجاح.";
}

?>   

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تعديل المدير</title>
    <link rel="stylesheet" href="css/login-superadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <h2>Edit admin</h2>

        <!-- عرض رسالة النجاح -->
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- نموذج تعديل بيانات المدير -->
        <form method="post" action="">
            <input type="hidden" name="admin_id" value="<?php echo $admin['CustomerID']; ?>">

            <label>Username</label>
            <input type="text" name="username" value="<?php echo $admin['username']; ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo $admin['Email']; ?>" required>

            <label>phone</label>
            <input type="text" name="phone" value="<?php echo $admin['phone']; ?>" required>

            <button type="submit" name="update_admin">Update</button>
        </form>

        <a href="admin-mangment.php">back to admin mangment</a>
    </div>
</body>
</html>
