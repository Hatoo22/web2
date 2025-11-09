<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'learner') {
  $msg  = 'Please log in as a learner to continue';
  $next = urlencode($_SERVER['REQUEST_URI']);
  header('Location: login.php?err='.urlencode($msg).'&next='.$next);
  exit;
}

include("db_connect.php");

// ✅ التحقق من وجود quiz_id في الطلب
$quiz_id = (int)($_GET['quiz_id'] ?? 0);
if (!$quiz_id) {
  echo "<h3>Error: Quiz ID required.</h3>";
  exit;
}

// ✅ جلب معلومات الاختبار (الموضوع + المعلم)
$stmt = $conn->prepare("
  SELECT q.id, t.topicName, u.firstName AS educatorFirst, u.lastName AS educatorLast
  FROM quiz q
  JOIN topic t ON q.topicID = t.id
  JOIN user u ON q.educatorID = u.id
  WHERE q.id = ?
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$res = $stmt->get_result();
$quizInfo = $res->fetch_assoc();
$stmt->close();

if (!$quizInfo) {
  echo "<h3>Error: Quiz not found in database.</h3>";
  exit;
}

// ✅ جلب الأسئلة
$stmt = $conn->prepare("SELECT * FROM quizquestion WHERE quizID=?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$res = $stmt->get_result();
$allQuestions = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$cnt = count($allQuestions);
if ($cnt == 0) {
  echo "<h3>This quiz has no questions.</h3>";
  exit;
}

// ✅ اختيار عشوائي لـ 5 أسئلة فقط إن وُجد أكثر
if ($cnt > 5) {
  shuffle($allQuestions);
  $selected = array_slice($allQuestions, 0, 5);
} else {
  $selected = $allQuestions;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Take Quiz</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .container {max-width:1100px; margin:0 auto; padding:0 16px;}
  .card {background:#fff; border:1px solid #e5e7eb; border-radius:12px;
         box-shadow:0 6px 20px rgba(0,0,0,.06); padding:20px; margin:24px auto; max-width:900px;}
  .question {border:1px solid #ccc; padding:12px; margin-bottom:12px;}
  button {background:#071b45; color:#fff; padding:10px 16px; border:none; border-radius:6px; cursor:pointer;}
  </style>
</head>
<body>
<header>
  <div class="logo"><img src="image/logo.png" alt="logo"><span>TechQuiz</span></div>
  <div class="logout"><a href="logout.php">Log out</a></div>
</header>

<main class="container">
  <div class="card">
    <h2>Take Quiz</h2>

    <!-- ✅ عرض تفاصيل الاختبار بوضوح -->
    <p><strong>Quiz ID:</strong> <?= htmlspecialchars($quiz_id) ?></p>
    <p><strong>Topic:</strong> <?= htmlspecialchars($quizInfo['topicName']) ?></p>
    <p><strong>Educator:</strong> <?= htmlspecialchars($quizInfo['educatorFirst'] . ' ' . $quizInfo['educatorLast']) ?></p>
    <hr><br>

    <form action="submit_quiz.php" method="post">
      <input type="hidden" name="quiz_id" value="<?= htmlspecialchars($quiz_id) ?>">

      <?php foreach($selected as $i => $q): ?>
        <div class="question">
          <p><strong>Question <?= ($i + 1) ?>:</strong> <?= nl2br(htmlspecialchars($q['question'])) ?></p>

          <?php if (!empty($q['questionFigureFileName'])): ?>
            <div><img src="uploads/<?= htmlspecialchars($q['questionFigureFileName']) ?>" style="max-width:300px"></div>
          <?php endif; ?>

          <div>
            <!-- ✅ تعديل أسماء الحقول لتكون مصفوفة -->
            <label><input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="A"> A) <?= htmlspecialchars($q['answerA']) ?></label><br>
            <label><input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="B"> B) <?= htmlspecialchars($q['answerB']) ?></label><br>
            <label><input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="C"> C) <?= htmlspecialchars($q['answerC']) ?></label><br>
            <label><input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="D"> D) <?= htmlspecialchars($q['answerD']) ?></label><br>
          </div>

          <input type="hidden" name="question_ids[]" value="<?= (int)$q['id'] ?>">
        </div>
      <?php endforeach; ?>

      <button type="submit">Submit Answers</button>
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
