<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'educator') {
  $msg  = 'Please log in as an educator to continue';
  $next = urlencode($_SERVER['REQUEST_URI']);
  header('Location: login.php?err='.urlencode($msg).'&next='.$next);
  exit;
}
include("db_connect.php");
$qid = (int)($_GET['question_id'] ?? 0);
if (!$qid) { echo "Question ID required."; exit; }
$stmt = $conn->prepare("SELECT qq.*, q.educatorID, q.id AS quiz_id FROM quizquestion qq JOIN quiz q ON qq.quizID=q.id WHERE qq.id=?");
$stmt->bind_param("i",$qid);
$stmt->execute();
$res = $stmt->get_result();
$question = $res->fetch_assoc();
$stmt->close();
if (!$question || $question['educatorID'] != $_SESSION['user_id']) { echo "Question not found or permission denied."; exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $questionText = trim($_POST['question'] ?? '');
  $answerA = trim($_POST['answerA'] ?? '');
  $answerB = trim($_POST['answerB'] ?? '');
  $answerC = trim($_POST['answerC'] ?? '');
  $answerD = trim($_POST['answerD'] ?? '');
  $correct = $_POST['correctAnswer'] ?? '';
  $remove_old = isset($_POST['remove_old_image']) && $_POST['remove_old_image']=='1';
  $figureFileName = $question['questionFigureFileName'];
  if ($remove_old && $figureFileName) {
    $oldPath = __DIR__ . "/uploads/" . $figureFileName;
    if (file_exists($oldPath)) unlink($oldPath);
    $figureFileName = null;
  }
  if (!empty($_FILES['questionFigure']) && $_FILES['questionFigure']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['questionFigure'];
    if ($f['error'] === UPLOAD_ERR_OK) {
      $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
      $newName = uniqid("q{$question['quiz_id']}_", true) . ($ext ? ".".$ext : "");
      if (!move_uploaded_file($f['tmp_name'], __DIR__ . "/uploads/" . $newName)) {
        $error = "Failed to upload new image.";
      } else {
        if ($figureFileName) {
          $oldPath = __DIR__ . "/uploads/" . $figureFileName;
          if (file_exists($oldPath)) unlink($oldPath);
        }
        $figureFileName = $newName;
      }
    } else {
      $error = "File upload error.";
    }
  }
  if (empty($error)) {
    $stmt = $conn->prepare("UPDATE quizquestion SET question=?,questionFigureFileName=?,answerA=?,answerB=?,answerC=?,answerD=?,correctAnswer=? WHERE id=?");
    $stmt->bind_param("sssssssi",$questionText,$figureFileName,$answerA,$answerB,$answerC,$answerD,$correct,$qid);
    $stmt->execute();
    $stmt->close();
    header("Location: quiz.php?quiz_id=".$question['quiz_id']);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .container{max-width:1100px;margin:0 auto;padding:0 16px}
  .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.06);padding:20px;margin:24px auto;max-width:900px}
  input[type=text], textarea, select {width:100%;padding:8px;border:1px solid #ddd;border-radius:6px}
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
    <h2>Edit Question #<?=htmlspecialchars($qid)?></h2>
    <?php if(!empty($error)): ?><div style="color:red"><?=htmlspecialchars($error)?></div><?php endif;?>
    <form method="post" enctype="multipart/form-data">
      <label>Question<textarea name="question" rows="4" required><?=htmlspecialchars($_POST['question'] ?? $question['question'])?></textarea></label>
      <?php if ($question['questionFigureFileName']): ?>
        <div>Current figure: <img src="uploads/<?=htmlspecialchars($question['questionFigureFileName'])?>" style="max-width:200px"></div>
        <label><input type="checkbox" name="remove_old_image" value="1"> Remove current image</label>
      <?php endif; ?>
      <label>Upload new figure<input type="file" name="questionFigure"></label>
      <label>Answer A<input type="text" name="answerA" required value="<?=htmlspecialchars($_POST['answerA'] ?? $question['answerA'])?>"></label>
      <label>Answer B<input type="text" name="answerB" required value="<?=htmlspecialchars($_POST['answerB'] ?? $question['answerB'])?>"></label>
      <label>Answer C<input type="text" name="answerC" required value="<?=htmlspecialchars($_POST['answerC'] ?? $question['answerC'])?>"></label>
      <label>Answer D<input type="text" name="answerD" required value="<?=htmlspecialchars($_POST['answerD'] ?? $question['answerD'])?>"></label>
      <label>Correct Answer
        <select name="correctAnswer" required>
          <option value="A" <?=($question['correctAnswer']=='A')?'selected':''?>>A</option>
          <option value="B" <?=($question['correctAnswer']=='B')?'selected':''?>>B</option>
          <option value="C" <?=($question['correctAnswer']=='C')?'selected':''?>>C</option>
          <option value="D" <?=($question['correctAnswer']=='D')?'selected':''?>>D</option>
        </select>
      </label>
      <button type="submit">Save Changes</button>
    </form>
    <p><a href="quiz.php?quiz_id=<?=htmlspecialchars($question['quiz_id'])?>">Back to Quiz</a></p>
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
