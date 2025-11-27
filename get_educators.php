<?php
session_start();
require __DIR__ . '/db_connect.php';

// Optional: Check user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

if(!isset($_POST['topic_id'])) {
    echo json_encode([]);
    exit;
}

$topic_id = (int)$_POST['topic_id'];

// Fetch all educators who have quizzes in this topic
$sql = "
    SELECT DISTINCT u.id, u.firstName, u.lastName
    FROM user u
    JOIN quiz q ON q.educatorID = u.id
    WHERE u.userType = 'educator' AND q.topicID = ?
    ORDER BY u.firstName, u.lastName
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

$educators = [];
while($row = $result->fetch_assoc()){
    $educators[] = $row;
}

echo json_encode($educators);
