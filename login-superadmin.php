<?php
session_start();
include 'config.php'; // تأكد من تضمين ملف الاتصال بقاعدة البيانات



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // تأكد من تنظيف المدخلات للحماية من هجمات SQL Injection
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    $query = "SELECT * FROM users WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // تحقق من كلمة المرور
        if (password_verify($password, $user['Password'])) {
            // تحقق إذا كان المستخدم super admin
            if ($user['Role'] === 'super admin') {
                $_SESSION['super admin'] = true; // تعيين المفتاح هنا
                $_SESSION['user_role'] = 'super admin';
                $_SESSION['user_id'] = $user['CustomerID'];
                header("Location: admin-mangment.php");
                exit;
            } else {
                echo "هذا المستخدم لا يمتلك صلاحيات super admin.";
            }
        } else {
            echo "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
    } else {
        echo "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
    }

    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title> Login superadmin</title>
    <link rel="stylesheet" href="css/login-superadmin.css">
</head>
<body>
    <div class="login-container">
        <h2>login superadmin</h2>
        <form method="post" action="">
            <label>Email:</label>
            <input type="email" name="email" required><br>

            <label>Password:</label>
            <input type="password" name="password" required><br>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
