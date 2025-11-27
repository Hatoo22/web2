<?php
include 'db_connect.php';
session_start();

// Check login
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'educator') {
  $msg  = 'Please log in as a educator to continue';
  $next = urlencode($_SERVER['REQUEST_URI']);
  header('Location: login.php?err='.urlencode($msg).'&next='.$next);
  exit;
}

$educatorID = $_SESSION['user_id'];

// Get educator info
$userQuery = "SELECT * FROM User WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $educatorID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Educator Dashboard - TechQuiz</title>
<link rel="stylesheet" href="style.css">

<style>
body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f9f9f9; color: #333; }
main { padding: 20px; }
.logo { width: 80px; margin-right: 15px; vertical-align: middle; }
.profile-pic { width: 120px; height: 120px; border-radius: 50%; margin-bottom: 10px; display: block; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
table, th, td { border: 1px solid #ddd; padding: 10px; }
th { background: #f0f0f0; }
.learner-pic { width: 50px; height: 50px; border-radius: 50%; }
.question-figure { width: 150px; display: block; margin: 10px auto; }
.review-form textarea { width: 90%; height: 60px; margin-bottom: 5px; }
.review-form button { margin-top: 5px; background: #004080; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 4px; }
.review-form button:hover { background: #0066cc; }
</style>
</head>

<body>

<header>
  <div class="logo">
    <img src="image/logo.png" alt="logo">
    <span>TechQuiz</span>
  </div>

  <div class="logout">
    <a href="logout.php">Log out </a> 
  </div>
</header>

<main>

<section class="user-info">
  <h2>Welcome, Prof. <?= htmlspecialchars($user['firstName']) ?></h2>

  <img src="image/<?= htmlspecialchars($user['photoFileName'] ?? 'default.png') ?>" 
       alt="Profile" class="profile-pic">

  <p><strong>First Name:</strong> <?= htmlspecialchars($user['firstName']) ?></p>
  <p><strong>Last Name:</strong> <?= htmlspecialchars($user['lastName']) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($user['emailAddress']) ?></p>

  <p><strong>Topics:</strong>
  <?php
    $topicQuery = "SELECT DISTINCT t.topicName 
                   FROM Topic t 
                   JOIN Quiz q ON q.topicID = t.id 
                   WHERE q.educatorID = ?";
    $stmt = $conn->prepare($topicQuery);
    $stmt->bind_param("i", $educatorID);
    $stmt->execute();
    $topics = $stmt->get_result();
    $topicList = [];
    while ($row = $topics->fetch_assoc()) { $topicList[] = $row['topicName']; }
    echo implode(', ', $topicList);
  ?>
  </p>
</section>

<section>
  <h2>My Quizzes</h2>
  <table>
    <tr>
      <th>Topic</th>
      <th>Questions</th>
      <th>Statistics</th>
      <th>Feedback</th>
    </tr>

<?php
$quizQuery = "
  SELECT q.id, t.topicName,
         (SELECT COUNT(*) FROM QuizQuestion WHERE quizID=q.id) AS questionCount,
         (SELECT COUNT(*) FROM TakenQuiz WHERE quizID=q.id) AS takenCount,
         (SELECT AVG(score) FROM TakenQuiz WHERE quizID=q.id) AS avgScore,
         (SELECT AVG(rating) FROM QuizFeedback WHERE quizID=q.id) AS avgRating
  FROM Quiz q
  JOIN Topic t ON q.topicID = t.id
  WHERE q.educatorID = ?";
$stmt = $conn->prepare($quizQuery);
$stmt->bind_param("i", $educatorID);
$stmt->execute();
$quizzes = $stmt->get_result();

if ($quizzes->num_rows > 0) {
  while ($quiz = $quizzes->fetch_assoc()) {
    echo "<tr>
            <td><a href='quiz.php?id={$quiz['id']}'>{$quiz['topicName']}</a></td>
            <td>{$quiz['questionCount']}</td>
            <td>";
    if ($quiz['takenCount'] > 0) {
      echo "{$quiz['takenCount']} takers, Avg Score: " . round($quiz['avgScore'], 1) . "%";
    } else {
      echo "quiz not taken yet";
    }
    echo "</td>
          <td>";
    if ($quiz['avgRating']) {
      echo round($quiz['avgRating'], 1) . "/5 - <a href='comment.php?quizID={$quiz['id']}'>View Comments</a>";
    } else {
      echo "no feedback yet";
    }
    echo "</td></tr>";
  }
} else {
  echo "<tr><td colspan='4'>No quizzes found.</td></tr>";
}
?>
  </table>
</section>

<section>
  <h2>Recommended Questions</h2>
  <table>
    <tr>
      <th>Topic</th>
      <th>Learner</th>
      <th>Question</th>
      <th>Review</th>
    </tr>

<?php
$recQuery = "
  SELECT rq.id, rq.quizID, rq.question, rq.questionFigureFileName,
         rq.answerA, rq.answerB, rq.answerC, rq.answerD, rq.correctAnswer,
         u.firstName AS learnerName, u.photoFileName AS learnerPhoto,
         t.topicName
  FROM RecommendedQuestion rq
  JOIN Quiz q ON rq.quizID = q.id
  JOIN Topic t ON q.topicID = t.id
  JOIN User u ON rq.learnerID = u.id
  WHERE q.educatorID = ? AND rq.status = 'pending'";
$stmt = $conn->prepare($recQuery);
$stmt->bind_param("i", $educatorID);
$stmt->execute();
$recs = $stmt->get_result();

if ($recs->num_rows > 0) {
  while ($rec = $recs->fetch_assoc()) {

    echo "<tr>
            <td>{$rec['topicName']}</td>
            <td>
              <img src='image/{$rec['learnerPhoto']}' class='learner-pic'><br>
              {$rec['learnerName']}
            </td>
            <td>
              <figure>";
    if (!empty($rec['questionFigureFileName'])) {
      echo "<img src='image/{$rec['questionFigureFileName']}' class='question-figure'>"; 
    }
    echo "<figcaption>" . htmlspecialchars($rec['question']) . "</figcaption>
          </figure>
          <p>A) " . htmlspecialchars($rec['answerA']) . "</p>
          <p>B) " . htmlspecialchars($rec['answerB']) . "</p>
          <p>C) " . htmlspecialchars($rec['answerC']) . "</p>
          <p>D) " . htmlspecialchars($rec['answerD']) . "</p>
          <p><strong>Correct Answer:</strong> " . htmlspecialchars($rec['correctAnswer']) . "</p>
        </td>

        <td>
          <form method='POST' action='review_recommendition.php' class='review-form ajax-review'>
            <input type='hidden' name='recommendID' value='{$rec['id']}'>
            <textarea name='comment' placeholder='Write your comment...'></textarea><br>

            <label><input type='radio' name='status' value='approved'> Approve</label><br>
            <label><input type='radio' name='status' value='disapproved'> Disapprove</label><br>

            <button type='submit'>Submit Review</button>
          </form>
        </td>
      </tr>";
  }
} else {
  echo "<tr><td colspan='4'>No recommended questions pending review.</td></tr>";
}
?>
  </table>
</section>

</main>

<footer>
  <div class="footer-left">
    <h4>Contact Us</h4>
    <p>📞 +966 5555 12345</p>
    <p>📧 TechQuiz@example.com</p>
  </div>
</footer>

<!-- 🔹 AJAX Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {

    $(".ajax-review").on("submit", function (e) {
        e.preventDefault(); 

        let form = $(this);
        let row = form.closest("tr");

        $.ajax({
            url: "review_recommendition.php",
            method: "POST",
            data: form.serialize(),
            success: function (response) {

                try {
                    let res = JSON.parse(response);

                    if (res.success) {
                        row.fadeOut(400, function() {
                            row.remove();
                        });
                    } else {
                        alert("Error: " + res.message);
                    }

                } catch (e) {
                    alert("Unexpected response from server.");
                }
            },
            error: function () {
                alert("Request failed. Please try again.");
            }
        });
    });

});
</script>

</body>
</html>
