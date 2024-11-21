<?php
session_start();
include 'config.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super admin') {
    header("Location: login-superadmin.php");
    exit();
}

// التأكد من استلام admin_id
if (!isset($_GET['admin_id'])) {
    header("Location: admin-management.php");
    exit();
}

$admin_id = intval($_GET['admin_id']); // تأكد من تحويل admin_id إلى عدد صحيح

// جلب بيانات المدير للتحرير
$query = "SELECT * FROM users WHERE CustomerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    header("Location: admin-management.php");
    exit();
}



// تحديث بيانات المدير
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);

    if ($username && $email && $phone) {
        $update_query = "UPDATE users SET username = ?, Email = ?, Phone = ? WHERE CustomerID = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssi", $username, $email, $phone, $admin_id);

        if ($update_stmt->execute()) {
            $success_message = "تم تحديث بيانات المدير بنجاح.";
        } else {
            $error_message = "حدث خطأ أثناء التحديث. حاول مرة أخرى.";
        }
    } else {
        $error_message = "يرجى إدخال جميع الحقول بشكل صحيح.";
    }
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
        <h2>Edit Admin</h2>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($admin['CustomerID']); ?>">

            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($admin['Email']); ?>" required>

            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>

            <button type="submit" name="update_admin">Update</button>
        </form>

        <a href="admin-management.php">Back to Admin Management</a>
    </div>
</body>
</html>
