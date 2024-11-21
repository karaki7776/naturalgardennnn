<?php
session_start();
include 'config.php'; 






if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super admin') {
  
    header("Location: login-superadmin.php");
    exit();
}



if (isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone']; 

    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // التحقق من وجود البريد الإلكتروني بالفعل
    $check_email_query = "SELECT * FROM users WHERE Email = ?";
    $check_stmt = $conn->prepare($check_email_query);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // إذا كان البريد الإلكتروني موجودًا بالفعل، يمكنك إظهار رسالة خطأ
        $error_message = "البريد الإلكتروني موجود بالفعل. يرجى استخدام بريد إلكتروني آخر.";
    } else {
        // إذا لم يكن البريد الإلكتروني موجودًا، يمكنك إدخال المدير
        $query = "INSERT INTO users (username, Email, Password, Role, Phone) VALUES (?, ?, ?, 'admin', ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $phone); // إضافة رقم الهاتف هنا
        $stmt->execute();
        $success_message = "تم إضافة المدير بنجاح.";
    }

    $check_stmt->close();
}

// حذف مدير
if (isset($_POST['delete_admin'])) {
    $admin_id = $_POST['admin_id'];

    $query = "DELETE FROM users WHERE CustomerID = ? AND Role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
}

// جلب قائمة المدراء الحاليين
$query = "SELECT * FROM users WHERE Role = 'admin'";
$result = $conn->query($query);

// تسجيل الخروج
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login-superadmin.php");
    exit();
}


        $_SESSION['role'] = 'super admin'; 
             
      
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>Admin mangment</title>
    <link rel="stylesheet" href="css/login-superadmin.css">
 <!--font awesome for icons------->
 <link rel="stylesheet " href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <!----favicom-->
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <!--remix icon-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
    rel="stylesheet">

</head>
<body>
    <div class="admin-container">
        <h2>Managing managers</h2>

        <!-- عرض رسالة الخطأ أو النجاح -->
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="get" action="all-users.php">
    <button type="submit">view user Details</button>
</form>

<h3>Add admin</h3>
        <form method="post" action="">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Phone:</label>
            <input type="text" name="phone" required> <!-- حقل إدخال رقم الهاتف -->

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit" name="add_admin">Add admin</button>
        </form>

        <h3>List of current managers:</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th> <!-- إضافة عمود رقم الهاتف -->
                <th>procedures</th>
            </tr>
            <?php while ($admin = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $admin['CustomerID']; ?></td>
                    <td><?php echo $admin['username']; ?></td>
                    <td><?php echo $admin['Email']; ?></td>
                    <td><?php echo $admin['phone']; ?></td> 
                    <td>
                    <form method="get" action="edit-admin.php">
    <input type="hidden" name="admin_id" value="<?php echo $admin['CustomerID']; ?>">
    <button type="submit"><i class='fa fa-pencil'></i> Edit</button>
</form>

                        <form method="post" action="">
                            <input type="hidden" name="admin_id" value="<?php echo $admin['CustomerID']; ?>">
                            <button type="submit" name="delete_admin">Remove</button>
                        </form>
                    </td> 
                </tr>
            <?php } ?>
        </table>

        <!-- زر تسجيل الخروج -->
        <form method="post" action="" >
            <button type="submit" name="logout" >logout <i class="fas fa-sign-out-alt"></i></button>
        </form>
    </div>
</body>
</html>
