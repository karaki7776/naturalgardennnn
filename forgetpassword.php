<?php
// الاتصال بقاعدة البيانات
include "config.php";

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// عند إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // التحقق من صحة الإدخال
    if (empty($email) || empty($confirm_password)) {
        echo "Email or password cannot be empty";
    } elseif ($new_password !== $confirm_password) {
        echo "Password not matched!";
    } else {
        // تحديث كلمة المرور في قاعدة البيانات
        $sql = "UPDATE users SET password='$new_password' WHERE email='$email'";
        $result = $conn->query($sql);
        




        if (!$result) {
            echo '<span style="color:green;">"Password update failed!" . $conn->error</span>';
        } else {
            header('refresh:3;url=login.php');
            echo '<span style="color:green;">Your Password has been successfully changed.</span>';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Forget Password</title>
    <link rel="stylesheet" href="css/forgot.css"> 
</head>
<body>
 

<div class="login-box"> <!--container---->
   <h2>Forget Password</h2>
    <form method="POST" action="">
    <div class="user-box">
        <input type="text" id="email" name="email" placeholder='Email' required><br>
      </div>
        <div class="user-box">
       
         <input type="password" id="password" name="password" placeholder="password"><br>
         </div>
         <div class="user-box">
        
        <input type="password" id="confirm_password" name="confirm_password" placeholder=" confirm password" required><br><br>
        </div>
       <a href="">
       <!-- <input type="submit" value="confirm">-->
        <span></span>
        <span></span>
        <span></span>
        <span></span>submit
       </a>
 </form>
       <a href="login.php">Login?</a>
</div>
</body>
</html>