<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'learner') {
  $msg  = 'Please log in as a learner to continue';
  $next = urlencode($_SERVER['REQUEST_URI']);
  header('Location: login.php?err='.urlencode($msg).'&next='.$next);
  exit;
}
include("db_connect.php");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: learner_home.php"); exit; }
$quiz_id = (int)($_POST['quiz_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comments = trim($_POST['comments'] ?? '');
if (!$quiz_id || $rating < 1 || $rating > 5) { $_SESSION['error']="Invalid feedback."; header("Location: learner_home.php"); exit; }
$ins = $conn->prepare("INSERT INTO quizfeedback (quizID,rating,comments,date) VALUES (?,?,?,NOW())");
$ins->bind_param("iis",$quiz_id,$rating,$comments);
$ins->execute();
$ins->close();
header("Location: learner_home.php");
exit;
?>
