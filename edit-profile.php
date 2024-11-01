<?php
session_start();

// الاتصال بقاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['CustomerID'])) {
    echo "No user is logged in.";
    exit();
}

$userID = $_SESSION['CustomerID'];

// جلب بيانات المستخدم
$query = "SELECT * FROM users WHERE CustomerID = '$userID'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// معالجة نموذج التعديل
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
   
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // معالجة الصورة
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileType = $_FILES['profile_picture']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Define allowed extensions and directory
        $allowedExts = array('jpg', 'jpeg', 'png');
        $uploadDir = 'categorie/';
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = $uploadDir . $newFileName;
        
        if (in_array($fileExtension, $allowedExts)) {
            if (move_uploaded_file($fileTmpPath, $uploadFileDir)) {
                $profilePicturePath = $uploadFileDir;
            } else {
                echo "Error uploading file.";
                $profilePicturePath = $user['profile_picture']; // Keep old picture if upload fails
            }
        } else {
            echo "Unsupported file type.";
            $profilePicturePath = $user['profile_picture']; // Keep old picture if type is not supported
        }
    } else {
        $profilePicturePath = $user['profile_picture']; // Keep old picture if no new file
    }
    
    $updateQuery = "UPDATE users SET username = '$name', phone = '$phone', Address = '$address', profile_picture = '$profilePicturePath' WHERE CustomerID = '$userID'";
    
    if ($conn->query($updateQuery) === TRUE) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/edit-profile.css">
</head>
<body>
    <div class="edit-container">
        <h1>Edit Profile</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" required placeholder="Name">
           
            
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required placeholder="Phone">
            
            <label for="address">Address: city / country / street</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['Address']); ?>" required  placeholder=" city / country / street">
            
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture">
            
            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
<?php    include 'footer.php';         ?>