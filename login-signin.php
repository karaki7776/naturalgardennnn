<?php
session_start();





// الاتصال بقاعدة البيانات
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "naturalgarden"; 

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'customer'; 
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    $stmt = $conn->prepare("INSERT INTO users (Username, Email, Password, Role, Address, Phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $role, $address, $phone);

    if ($stmt->execute()) {
        $userID = $conn->insert_id; // الحصول على معرف المستخدم الجديد
        $_SESSION['CustomerID'] = $userID; // تعيين معرف المستخدم في الجلسة
        $_SESSION['username'] = $name; // تخزين الاسم في الجلسة
        $_SESSION['role'] = $role; // تخزين الدور في الجلسة

        // توجيه المستخدم إلى صفحة المستخدم العادي
        header('location:homepage.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}



if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // التحقق من كلمة المرور
        if (password_verify($password, $row['Password'])) {
            // تخزين بيانات الجلسة
            $_SESSION['CustomerID'] = $row['CustomerID'];
            $_SESSION['username'] = $row['Username'];
            $_SESSION['role'] = $row['Role'];

          
                header('location:homepage.php');
            
            exit();
        } else {
            echo "<div class='invalid-password'>";
            echo "Invalid password.";
            echo "</div>";
        }
    } else {
        echo '<p style="color: red;">No user found with this email.</p>';
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/sheet.css">
    <title>Login & Register</title>
</head>
<body>

    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="" method="POST">
                <h1>Create Account</h1>
                <span>or use your email for registration</span>
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="address" placeholder="Address" required>
                <input type="text" name="phone" placeholder="Phone" required>
                <button type="submit" class="registerBtn" name="register">Sign Up</button>
            </form>
        </div>

        <div class="form-container sign-in">
            <form action="" method="POST">
                <h1>Sign In</h1>
                <span>or use your email password</span>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <a href="forgetpassword.php">Forget Your Password?</a>
                <button type="submit" class="loginBtn" name="login">Sign In</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your personal details to use all site features</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Register with your personal details to use all site features</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
