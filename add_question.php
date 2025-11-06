<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'educator') {
  $msg  = 'Please log in as an educator to continue';
  $next = urlencode($_SERVER['REQUEST_URI']);
  header('Location: login.php?err='.urlencode($msg).'&next='.$next);
  exit;
}
include("db_connect.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $quiz_id = (int)($_POST['quiz_id'] ?? 0);
  $question = trim($_POST['question'] ?? '');
  $answerA = trim($_POST['answerA'] ?? '');
  $answerB = trim($_POST['answerB'] ?? '');
  $answerC = trim($_POST['answerC'] ?? '');
  $answerD = trim($_POST['answerD'] ?? '');
  $correct = $_POST['correctAnswer'] ?? '';
  if ($quiz_id && $question && $answerA && $answerB && $answerC && $answerD && in_array($correct,['A','B','C','D'])) {
    $figureFileName = null;
    if (!empty($_FILES['questionFigure']) && $_FILES['questionFigure']['error'] !== UPLOAD_ERR_NO_FILE) {
      $f = $_FILES['questionFigure'];
      if ($f['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $newName = uniqid("q{$quiz_id}_", true) . ($ext ? ".".$ext : "");
        if (!move_uploaded_file($f['tmp_name'], __DIR__ . "/uploads/" . $newName)) {
          $error = "Failed to upload image.";
        } else {
          $figureFileName = $newName;
        }
      } else {
        $error = "File upload error.";
      }
    }
    if (empty($error)) {
      $stmt = $conn->prepare("INSERT INTO quizquestion (quizID,question,questionFigureFileName,answerA,answerB,answerC,answerD,correctAnswer) VALUES (?,?,?,?,?,?,?,?)");
      $stmt->bind_param("isssssss",$quiz_id,$question,$figureFileName,$answerA,$answerB,$answerC,$answerD,$correct);
      $stmt->execute();
      $stmt->close();
      header("Location: quiz.php?quiz_id=".$quiz_id);
      exit;
    }
  } else {
    $error = "Missing or invalid fields.";
  }
}
$quiz_id = (int)($_GET['quiz_id'] ?? 0);
$stmt = $conn->prepare("SELECT id FROM quiz WHERE id=? AND educatorID=?");
$stmt->bind_param("ii",$quiz_id,$_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$quiz = $res->fetch_assoc();
$stmt->close();
if (!$quiz) {
  echo "Quiz ID is required or you don't have permission.";
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .container{max-width:1100px;margin:0 auto;padding:0 16px}
  .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.06);padding:20px;margin:24px auto;max-width:900px}
  label{display:block;margin-bottom:8px}
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
    <h2>Add Question to Quiz #<?=htmlspecialchars($quiz_id)?></h2>
    <?php if(!empty($error)): ?><div style="color:red"><?=htmlspecialchars($error)?></div><?php endif;?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="quiz_id" value="<?=htmlspecialchars($quiz_id)?>">
      <label>Question<textarea name="question" rows="4" required><?=htmlspecialchars($_POST['question'] ?? '')?></textarea></label>
      <label>Figure (optional)<input type="file" name="questionFigure"></label>
      <label>Answer A<input type="text" name="answerA" required value="<?=htmlspecialchars($_POST['answerA'] ?? '')?>"></label>
      <label>Answer B<input type="text" name="answerB" required value="<?=htmlspecialchars($_POST['answerB'] ?? '')?>"></label>
      <label>Answer C<input type="text" name="answerC" required value="<?=htmlspecialchars($_POST['answerC'] ?? '')?>"></label>
      <label>Answer D<input type="text" name="answerD" required value="<?=htmlspecialchars($_POST['answerD'] ?? '')?>"></label>
      <label>Correct Answer
        <select name="correctAnswer" required>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
        </select>
      </label>
      <button type="submit">Add Question</button>
    </form>
    <p><a href="quiz.php?quiz_id=<?=htmlspecialchars($quiz_id)?>">Back to Quiz</a></p>
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
