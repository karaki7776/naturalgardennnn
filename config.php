<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "naturalgarden"; 

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
    
}
    ?>