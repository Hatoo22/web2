<?php
session_start();
include("db_connect.php");

// --------------------------------------------------
// CHECK USER LOGIN AND ROLE
// --------------------------------------------------
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'educator'){
    // المستخدم مو معلّم → يخرج من الصفحة
    //header("Location: login.php?error=unauthorized");
    exit();
}

// --------------------------------------------------
// CHECKS THE ID THAT IS SENT IN THE QUERY STRING
// --------------------------------------------------
$quizID = isset($_GET['quizID']) ? intval($_GET['quizID']) : 0;
$questionID = isset($_GET['questionID']) ? intval($_GET['questionID']) : 0;

if($quizID <= 0 || $questionID <= 0){
    // لو ما وصل رقم الكويز أو السؤال → ما يسوي شيء
    exit();
}

// --------------------------------------------------
// DELETES QUESTION FIGURE IMAGE FROM THE SYSTEM
// --------------------------------------------------
$getFile = mysqli_query($conn, "SELECT questionFigureFileName FROM QuizQuestion WHERE id = $questionID");
if($getFile && mysqli_num_rows($getFile) > 0){
    $fileRow = mysqli_fetch_assoc($getFile);
    $fileName = $fileRow['questionFigureFileName'];

    // حذف الصورة من مجلد uploads إذا موجودة
    if(!empty($fileName) && file_exists("uploads/" . $fileName)){
        unlink("uploads/" . $fileName);
    }
}

// --------------------------------------------------
// DELETES THE CORRESPONDING QUESTION IN THE DATABASE
// --------------------------------------------------
mysqli_query($conn, "DELETE FROM QuizQuestion WHERE id = $questionID");

// --------------------------------------------------
// REDIRECTS TO THE QUIZ PAGE
// --------------------------------------------------
header("Location: quiz.php?id=$quizID");
exit();
?>
