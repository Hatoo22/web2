<?php
// ====================================================================
// 🔹 Start session and include DB connection
// ====================================================================
session_start();
include 'db_connect.php';

// --------------------------------------------------------------------
// 🔹 Check if educator is logged in
// --------------------------------------------------------------------
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'educator') {
    echo json_encode(["status" => "error", "message" => "unauthorized"]);
    exit();
}

// --------------------------------------------------------------------
// 🔹 Must be POST (AJAX request)
// --------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header("Content-Type: application/json");

    $recommendID = intval($_POST['recommendID']);
    $status = $_POST['status'] ?? '';
    $comment = trim($_POST['comment'] ?? '');

    // تحقق من أن القيم المطلوبة موجودة
    if (empty($recommendID) || empty($status)) {
        echo json_encode(["status" => "error", "message" => "missingData"]);
        exit();
    }

    // ----------------------------------------------------------------
    // 🔹 1. استرجاع بيانات السؤال المقترح
    // ----------------------------------------------------------------
    $query = "SELECT * FROM recommendedquestion WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $recommendID);
    $stmt->execute();
    $result = $stmt->get_result();
    $recommend = $result->fetch_assoc();

    if (!$recommend) {
        echo json_encode(["status" => "error", "message" => "notfound"]);
        exit();
    }

    // ----------------------------------------------------------------
    // 🔹 2. تحديث الحالة والتعليق
    // ----------------------------------------------------------------
    $update = "UPDATE recommendedquestion SET status = ?, comments = ? WHERE id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssi", $status, $comment, $recommendID);
    $stmt->execute();

    // ----------------------------------------------------------------
    // 🔹 3. إذا تمت الموافقة → إضافة السؤال لجدول quizquestion
    // ----------------------------------------------------------------
    if ($status === 'approved') {
        $insert = "INSERT INTO quizquestion 
                    (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param(
            "isssssss",
            $recommend['quizID'],
            $recommend['question'],
            $recommend['questionFigureFileName'],
            $recommend['answerA'],
            $recommend['answerB'],
            $recommend['answerC'],
            $recommend['answerD'],
            $recommend['correctAnswer']
        );
        $stmt->execute();
    }

    // ----------------------------------------------------------------
   
    // ----------------------------------------------------------------
    echo json_encode(["status" => "success", "message" => "reviewSaved"]);
    exit();

} else {
    // إذا مو POST
    echo json_encode(["status" => "error", "message" => "invalidRequest"]);
    exit();
}
?>
