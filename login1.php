<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db_connect.php"); // ملف الاتصال بقاعدة البيانات

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $selectedRole = $_POST['role']; // <-- دور المستخدم من النموذج

    // التحقق من وجود المستخدم في قاعدة البيانات
    $query = "SELECT * FROM user WHERE emailAddress = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // تحقق من كلمة المرور
        if (password_verify($password, $row['password'])) {

            // تحقق من تطابق النوع مع ما اختاره المستخدم
            if ($row['userType'] !== $selectedRole) {
                header("Location: login.php?error=wrongrole");
                exit();
            }

            // تخزين بيانات المستخدم في الجلسة
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_type'] = $row['userType'];

            // التوجيه حسب النوع
            if ($row['userType'] == 'educator') {
                header("Location: educator.php");///////// change to php
            } elseif ($row['userType'] == 'learner') {
                header("Location: LearnerHomepage.php"); ////////change to php
            }
            exit();

        } else {
            header("Location: login.php?error=invalid");
            exit();
        }
    } else {
        header("Location: login.php?error=invalid");
        exit();
    }
}

?>

