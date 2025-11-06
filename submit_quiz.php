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
$question_ids = $_POST['question_ids'] ?? [];
if (!$quiz_id || !is_array($question_ids) || count($question_ids)==0) { echo "Invalid submission."; exit; }
$placeholders = implode(',', array_fill(0, count($question_ids), '?'));
$sql = "SELECT id, correctAnswer FROM quizquestion WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);
$types = str_repeat('i', count($question_ids));
$params = [];
foreach($question_ids as $k=>$v) $params[] = (int)$v;
$refs = [];
$refs[] = &$types;
foreach($params as $i => $val) $refs[] = &$params[$i];
call_user_func_array([$stmt,'bind_param'],$refs);
$stmt->execute();
$res = $stmt->get_result();
$corrects = [];
while ($r = $res->fetch_assoc()) { $corrects[$r['id']] = $r['correctAnswer']; }
$stmt->close();
$total = count($question_ids);
$scoreCount = 0;
foreach ($question_ids as $qid) {
  $qid = (int)$qid;
  $userAns = $_POST['answer_'.$qid] ?? null;
  $correct = $corrects[$qid] ?? null;
  if ($userAns && $correct && $userAns === $correct) $scoreCount++;
}
$scorePercent = round(($scoreCount/$total)*100);
$ins = $conn->prepare("INSERT INTO takenquiz (quizID,score) VALUES (?,?)");
$ins->bind_param("ii",$quiz_id,$scorePercent);
$ins->execute();
$ins->close();
$reaction = '';
if ($scorePercent >= 80) $reaction = 'videos/excellent.mp4';
elseif ($scorePercent >= 50) $reaction = 'videos/good.mp4';
else $reaction = 'videos/try_again.mp4';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Quiz Result</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .container{max-width:1100px;margin:0 auto;padding:0 16px}
  .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.06);padding:20px;margin:24px auto;max-width:900px}
  button{background:#071b45;color:#fff;padding:10px 16px;border:none;border-radius:6px;cursor:pointer}
  </style>
</head>
<body>
<header>
  <div class="logo"><img src="image/logo.png" alt="logo"><span>TechQuiz</span></div>
  <div class="logout"><a href="logout.php">Log out</a></div>
</header>
<main class="container">
  <div class="card">
    <h2>Your Score: <?=htmlspecialchars($scorePercent)?>%</h2>
    <p>Correct: <?=htmlspecialchars($scoreCount)?> / <?=htmlspecialchars($total)?></p>
    <?php if($reaction): ?>
      <div>
        <p>Reaction:</p>
        <video width="480" controls>
          <source src="<?=htmlspecialchars($reaction)?>" type="video/mp4">
        </video>
      </div>
    <?php endif; ?>
    <form action="submit_feedback.php" method="post">
      <input type="hidden" name="quiz_id" value="<?=htmlspecialchars($quiz_id)?>">
      <label>Rate this quiz:
        <select name="rating" required>
          <option value="5">5 - Excellent</option>
          <option value="4">4 - Good</option>
          <option value="3">3 - OK</option>
          <option value="2">2 - Poor</option>
          <option value="1">1 - Very Poor</option>
        </select>
      </label><br><br>
      <label>Comments:<br><textarea name="comments" rows="4" cols="60"></textarea></label><br><br>
      <button type="submit">Submit Feedback & Return to Homepage</button>
    </form>
  </div>
</main>
<footer>
  <div class="footer-left">
    <h4>Contact Us</h4>
    <p>📞 +966 5555 12345</p>
    <p>📧 TechQuiz@example.com</p>
  </div>
</footer>
</body>
</html>
