
<?php
// save_recommend.php
session_start();
require __DIR__ . '/db_connect.php';

// لازم يكون مسجل كـ learner
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'learner') {
  $msg  = 'Please log in as a learner to continue';
  $next = urlencode('Recommend.php');
  header('Location: login.php?err=' . urlencode($msg) . '&next=' . $next);
  exit;
}

$learnerID = (int)$_SESSION['user_id'];

// =============== جمع المدخلات ===============
$question      = trim($_POST['question'] ?? '');
$answerA       = trim($_POST['answerA'] ?? '');
$answerB       = trim($_POST['answerB'] ?? '');
$answerC       = trim($_POST['answerC'] ?? '');
$answerD       = trim($_POST['answerD'] ?? '');
$correctAnswer = $_POST['correctAnswer'] ?? '';
$topicID       = (int)($_POST['topic_id'] ?? 0);
$educatorID    = (int)($_POST['educator_id'] ?? 0);

// تحقّق بسيط
if ($question === '' || $answerA === '' || $answerB === '' || $answerC === '' || $answerD === '' ||
    !in_array($correctAnswer, ['A','B','C','D'], true) || $topicID <= 0 || $educatorID <= 0) {
  header('Location: Recommend.php?err=missing');
  exit;
}

// =============== نجيب/ننشئ quiz مناسب ===============
$quizID = 0;

// ابحث عن كويز لنفس التوبيك والمعلم (أحدث واحد)
$sqlQuiz = "SELECT id FROM quiz WHERE topicID = ? AND educatorID = ? ORDER BY id DESC LIMIT 1";
$stmtQ = $conn->prepare($sqlQuiz);
$stmtQ->bind_param('ii', $topicID, $educatorID);
$stmtQ->execute();
$resQ = $stmtQ->get_result();

if ($rowQ = $resQ->fetch_assoc()) {
  $quizID = (int)$rowQ['id'];
} else {
  // ما فيه كويز — ننشئ Placeholder تلقائيًا
  $sqlCreateQuiz = "INSERT INTO quiz (topicID, educatorID) VALUES (?, ?)";
  $stmtCQ = $conn->prepare($sqlCreateQuiz);
  $stmtCQ->bind_param('ii', $topicID, $educatorID);
  $stmtCQ->execute();
  $quizID = (int)$conn->insert_id;
}

// =============== رفع صورة السؤال (اختياري) ===============
$figureFile = null;
if (!empty($_FILES['figure']['name'])) {
  $allowed = ['png','jpg','jpeg','gif'];
  $maxSize = 2 * 1024 * 1024; // 2MB

  $name = $_FILES['figure']['name'];
  $tmp  = $_FILES['figure']['tmp_name'];
  $size = $_FILES['figure']['size'];
  $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

  if (in_array($ext, $allowed, true) && $size <= $maxSize) {
    $safeName = 'rq_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    $destDir = __DIR__ . '/image';
    if (!is_dir($destDir)) { @mkdir($destDir, 0777, true); }

    if (move_uploaded_file($tmp, $destDir . '/' . $safeName)) {
      $figureFile = $safeName; // نخزّن الاسم فقط
    }
  }
}

// =============== إدخال الطلب في recommendedquestion بحالة Pending ===============
$status   = 'pending';
$comments = null;

$sqlIns = "INSERT INTO recommendedquestion
           (quizID, learnerID, question, questionFigureFileName,
            answerA, answerB, answerC, answerD, correctAnswer,
            status, comments)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sqlIns);
$stmt->bind_param(
  'iisssssssss',
  $quizID,
  $learnerID,
  $question,
  $figureFile,
  $answerA,
  $answerB,
  $answerC,
  $answerD,
  $correctAnswer,
  $status,
  $comments
);
$stmt->execute();

// =============== تحويل لصفحة المتعلّم ===============
header('Location: LernerHomepage.php?added=1');
exit;
