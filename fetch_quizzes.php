

<?php
session_start();

if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'learner') {
    // لو مو ليرنر نعطيه مصفوفة فاضية
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([]);
    exit;
}

include("db_connect.php");

header('Content-Type: application/json; charset=utf-8');

$topicId = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

$query = "SELECT q.id AS quiz_id, t.topicName,
                 u.firstName AS educatorFirst, u.lastName AS educatorLast,
                 COUNT(qq.id) AS questionCount
          FROM quiz q
          JOIN topic t ON q.topicID = t.id
          JOIN user  u ON q.educatorID = u.id
          LEFT JOIN quizquestion qq ON qq.quizID = q.id";

if ($topicId > 0) {
    $query .= " WHERE q.topicID = $topicId";
}

$query .= " GROUP BY q.id, t.topicName, u.firstName, u.lastName";

$quizzes = [];
$res = mysqli_query($conn, $query);
while ($res && $row = mysqli_fetch_assoc($res)) {
    $quizzes[] = [
        'quiz_id'       => (int)$row['quiz_id'],
        'topicName'     => $row['topicName'],
        'educatorFirst' => $row['educatorFirst'],
        'educatorLast'  => $row['educatorLast'],
        'questionCount' => (int)$row['questionCount'],
    ];
}

echo json_encode($quizzes); 
