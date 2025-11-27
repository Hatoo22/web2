

 <?php
session_start();
require __DIR__ . '/db_connect.php';

// (اختياري) تأكيد أنه ليرنر
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'learner') {
  $msg  = 'Please log in as a learner to continue';
  $next = urlencode($_SERVER['REQUEST_URI']);
  header('Location: login.php?err='.urlencode($msg).'&next='.$next);
  exit;
}

// ==== جلب التوبيكس ====
$topics = [];
$sqlTopics = "SELECT id, topicName FROM topic ORDER BY topicName ASC";
$resT = $conn->query($sqlTopics);
while ($resT && $row = $resT->fetch_assoc()) { $topics[] = $row; }

// ==== جلب أسماء المعلمين (educators) ====
$educators = [];
$sqlEdu = "SELECT id, firstName, lastName FROM `user` WHERE userType='educator' ORDER BY firstName, lastName";
$resE = $conn->query($sqlEdu);
while ($resE && $row = $resE->fetch_assoc()) { $educators[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recommend Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { font-family: Tahoma, Arial; background:#ffffff; margin:0; }
    .container { display:flex; justify-content:center; align-items:flex-start; padding:30px 0 80px 0;}
    .box { border:2px solid #000; background:#ebebed; padding:20px; width:350px; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,.2);}
    label { font-weight:bold; display:block; margin-top:10px; }
    input[type=text], select, textarea { width:100%; padding:5px; margin-top:5px; }
    input[type=file]{ margin-top:5px; }
    .btn, button{ background:#0d6efd; color:#fff; padding:7px 10px; border-radius:4px; text-decoration:none; cursor:pointer; font-size:16px; transition:.3s; margin-top:15px;}
    .btn:hover, button:hover{ background:#084298; }
  </style>
</head>
<body>
<header>
  <div class="logo">
    <img src="image/logo.png" alt="logo">
    <span>TechQuiz</span>
  </div>

  <div class="navbar">
    <a href="LearnerHomepage.php">Home</a>
  </div>

  <div class="logout">
    <a href="logout.php">Log out</a>
  </div>
</header>

<div class="container">
  <div class="box">
    <h3>Recommend a Question</h3>

    <!--
      ملاحظة: غيّري action لملف الحفظ لاحقًا (مثلاً save_recommend.php)
      وحطي عملية الإدخال هناك بـ INSERT INTO recommendedquestion (...)
    -->
 <form action="/web2/ويب2/save_recommend.php" method="post" enctype="multipart/form-data">


      <label>Question:</label>
      <textarea name="question" rows="3" placeholder="Write your question here..." required></textarea>

      <label>Upload Question Figure:</label>
      <input type="file" name="figure">

      <label>Answer A:</label>
      <input type="text" name="answerA" placeholder="Enter option A" required>
      <label>Answer B:</label>
      <input type="text" name="answerB" placeholder="Enter option B" required>
      <label>Answer C:</label>
      <input type="text" name="answerC" placeholder="Enter option C" required>
      <label>Answer D:</label>
      <input type="text" name="answerD" placeholder="Enter option D" required>

      <label>Correct Answer:</label>
      <select name="correctAnswer" required>
        <option value="" disabled selected>Choose the correct answer</option>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
      </select>

      <!-- ===== Topic (من الداتابيس) ===== -->
      <label>Topic:</label>
      <select name="topic_id" required>
        <option value="" disabled selected>Select a topic</option>
        <?php if ($topics): ?>
          <?php foreach ($topics as $t): ?>
            <option value="<?= (int)$t['id'] ?>">
              <?= htmlspecialchars($t['topicName'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        <?php else: ?>
          <option disabled>No topics found</option>
        <?php endif; ?>
      </select>

      <!-- ===== Educator (من الداتابيس) ===== -->
      <label>Educator:</label>
      <select name="educator_id" required>
        <option value="" disabled selected>Select an educator</option>
        <?php if ($educators): ?>
          <?php foreach ($educators as $e): ?>
            <option value="<?= (int)$e['id'] ?>">
              <?= htmlspecialchars($e['firstName'].' '.$e['lastName'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        <?php else: ?>
          <option disabled>No educators found</option>
        <?php endif; ?>
      </select>

      <button type="submit" class="btn">Recommend</button>
    </form>
  </div>
</div>

<footer>
  <div class="footer-left">
    <h4>Contact Us</h4>
    <p>📞 +966 5555 12345</p>
    <p>📧 TechQuiz@example.com</p>
  </div>
</footer>
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // When the topic dropdown changes
    $('select[name="topic_id"]').on('change', function() {
        var topicID = $(this).val(); // get selected topic id
        if(topicID) {
            $.ajax({
                url: 'get_educators.php', // PHP page that returns educators as JSON
                type: 'POST',
                data: { topic_id: topicID },
                dataType: 'json',
                success: function(data) {
                    var $educatorSelect = $('select[name="educator_id"]');
                    $educatorSelect.empty(); // clear previous options
                    $educatorSelect.append('<option value="" disabled selected>Select an educator</option>');
                    if(data.length > 0){
                        $.each(data, function(i, educator) {
                            $educatorSelect.append(
                                $('<option>', {
                                    value: educator.id,
                                    text: educator.firstName + ' ' + educator.lastName
                                })
                            );
                        });
                    } else {
                        $educatorSelect.append('<option disabled>No educators found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', error);
                }
            });
        }
    });
});
</script>
</body>
</html>

