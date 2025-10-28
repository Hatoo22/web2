
<?php
// معلومات الاتصال بقاعدة البيانات
$servername = "localhost";      
$username   = "root";           
$password   = "root";               
$dbname     = "quizzes";  // اسم قاعدة البيانات

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
