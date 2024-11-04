<?php
session_start();


if (!isset($_SESSION['CustomerID'])) {
    header("location:login-signin.php");
}

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super admin') {
        $admin = true;
    } else {
        $admin = false;
    }
} else {
    $admin = false; 
}


// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// تأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['CustomerID'])) {
    echo "No user is logged in.";
    exit();
}

$userID = $_SESSION['CustomerID'];

// جلب معلومات المستخدم
$sql = "SELECT * FROM users WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profilePicture = isset($user['profile_picture']) ? $user['profile_picture'] : 'default-profile.png';
    $username = isset($user['username']) ? $user['username'] : 'Unknown User';
    $email = isset($user['Email']) ? $user['Email'] : 'No Email';
    $phone = isset($user['phone']) ? $user['phone'] : 'No Phone';
    $address = isset($user['Address']) ? $user['Address'] : 'No Address';
} else {
    echo "User not found.";
    exit();
}

$conn->close();

// عملية تسجيل الخروج
if (isset($_POST['logout'])) {
    session_destroy(); // تدمير الجلسة الحالية
    header("Location: login.php"); // إعادة التوجيه إلى صفحة تسجيل الدخول
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
      <!--font awesome for icons------->
      <link rel="stylesheet " href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <!----favicom-->
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <!--remix icon-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
    rel="stylesheet">
  
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    
<a href="homepage.php" class="back-arrow">
    <i class="fas fa-arrow-left"></i>
</a>

    <div class="profile-container">
        <!-- صورة المستخدم -->
        <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" class="profile-pic">
        
        <!-- معلومات المستخدم -->
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($username); ?></h2>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
            <p>Phone: <?php echo htmlspecialchars($phone); ?></p>
            <p>Address: <?php echo htmlspecialchars($address); ?></p>
        </div>
<?php 
        if($admin == false ){
            echo"<div class='order-link'>
            <a href='orders.php'>View Orders</a>
        </div>";}     
        
        

   else{ 
    }  
         ?>
</div>
    <!-- رابط تعديل الملف الشخصي -->
    <div class="edit-link">
        <a href="edit-profile.php">Edit Profile</a>
    </div>

    <!-- زر تسجيل الخروج -->
    <div class="logout-container">
        <form action="login-signin.php" method="POST">
            <button type="submit" name="logout" class="logout-btn">Logout  <i class="fas fa-sign-out-alt"></i></button>
        </form>
    </div>

</body>
</html>
