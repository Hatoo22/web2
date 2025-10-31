<?php
// ====================================================================
// 🔹 Start the PHP session to manage user authentication
// ===================================================================
include 'db_connect.php';

session_start();

// Check if a user is logged in and if the user type is "educator"
// This ensures that only educators can access this page
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'educator') {
    // Redirect unauthorized users back to the login page
    header("Location: login.php?error=unauthorized");
    exit();
}

// Include the database connection file

// Retrieve the educator's ID from the session variable
$educatorID = $_SESSION['user_id'];

// ====================================================================
// 🔹 Retrieve educator personal information from the database
// ====================================================================
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
/* =========================================================
   🔹 Basic Page Styling (CSS)
   ========================================================= */
body {
  font-family: Arial, sans-serif;
  margin: 0; padding: 0;
  background: #f9f9f9;
  color: #333;
}
main { padding: 20px; }

/* ---------- Header Style ---------- */
.logo {
  width: 80px;   
  height: auto;
  margin-right: 15px;
  vertical-align: middle;
}

/* ---------- Profile Section ---------- */
.profile-pic {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  margin-bottom: 10px;
  display: block;
}

/* ---------- Tables ---------- */
table {
  width: 100%;
  border-collapse: collapse;
  margin: 15px 0;
}
table, th, td {
  border: 1px solid #ddd;
  padding: 10px;
}
th { background: #f0f0f0; }

/* ---------- Images for Learners ---------- */
.learner-pic {
  width: 50px;
  height: 50px;
  border-radius: 50%;
}

/* ---------- Image for Question Figures ---------- */
.question-figure {
  width: 150px;
  display: block;
  margin: 10px auto;
}

/* ---------- Review Form Styling ---------- */
.review-form textarea {
  width: 90%;
  height: 60px;
  margin-bottom: 5px;
}
.review-form button {
  margin-top: 5px;
  background: #004080;
  color: white;
  border: none;
  padding: 6px 12px;
  cursor: pointer;
  border-radius: 4px;
}
.review-form button:hover { background: #0066cc; }

/* ---------- Footer Styling ---------- */

</style>
</head>

<body>
<!-- ============================================================
     🔹 Header Section (Logo and Logout button)
     ============================================================ -->
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
  <!-- ============================================================
       🔹 Educator Info Section
       Displays educator profile information from the database
       ============================================================ -->
  <section class="user-info">
    <h2>Welcome, Prof. <?= htmlspecialchars($user['firstName']) ?></h2>

    <!-- Display educator's profile image -->
    <img src="uploads/<?= htmlspecialchars($user['photoFileName'] ?? 'default.png') ?>" 
         alt="Profile" class="profile-pic">

    <!-- Display basic user info -->
    <p><strong>First Name:</strong> <?= htmlspecialchars($user['firstName']) ?></p>
    <p><strong>Last Name:</strong> <?= htmlspecialchars($user['lastName']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['emailAddress']) ?></p>

    <!-- Display educator’s assigned topics -->
    <p><strong>Topics:</strong>
      <?php
      // Fetch all distinct topics related to this educator
      $topicQuery = "SELECT DISTINCT t.topicName 
                     FROM Topic t 
                     JOIN Quiz q ON q.topicID = t.id 
                     WHERE q.educatorID = ?";
      $stmt = $conn->prepare($topicQuery);
      $stmt->bind_param("i", $educatorID);
      $stmt->execute();
      $topics = $stmt->get_result();
      $topicList = [];
      while ($row = $topics->fetch_assoc()) {
        $topicList[] = $row['topicName'];
      }
      // Join all topic names as a comma-separated list
      echo implode(', ', $topicList);
      ?>
    </p>
  </section>

  <!-- ============================================================
       🔹 My Quizzes Table
       Displays all quizzes created by this educator
       ============================================================ -->
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
      // Query retrieves quizzes, number of questions, and feedback statistics
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

      // Loop through all quizzes and display them in the table
      if ($quizzes->num_rows > 0) {
        while ($quiz = $quizzes->fetch_assoc()) {
          echo "<tr>
                  <!-- Quiz topic name links to quiz.php -->
                  <td><a href='quiz.php?id={$quiz['id']}'>{$quiz['topicName']}</a></td>
                  <td>{$quiz['questionCount']}</td>
                  <td>";
          // Display number of takers and average score
          if ($quiz['takenCount'] > 0) {
            echo "{$quiz['takenCount']} takers, Avg Score: " . round($quiz['avgScore'], 1) . "%";
          } else {
            echo "quiz not taken yet";
          }
          echo "</td>
                <td>";
          // Display average feedback rating with link to comments.php
          if ($quiz['avgRating']) {
            echo round($quiz['avgRating'], 1) . "/5 - <a href='comments.php?quizID={$quiz['id']}'>View Comments</a>";
          } else {
            echo "no feedback yet";
          }
          echo "</td></tr>";
        }
      } else {
        // If no quizzes found
        echo "<tr><td colspan='4'>No quizzes found for this educator.</td></tr>";
      }
      ?>
    </table>
  </section>

  <!-- ============================================================
       🔹 Recommended Questions Table
       Displays pending recommended questions from learners
       ============================================================ -->
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
      // Fetch all recommended questions for this educator with status 'pending'
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

      // Loop through all pending recommended questions
      if ($recs->num_rows > 0) {
        while ($rec = $recs->fetch_assoc()) {
          echo "<tr>
                  <td>{$rec['topicName']}</td>
                  <td>
                    <img src='uploads/{$rec['learnerPhoto']}' class='learner-pic'><br>
                    {$rec['learnerName']}
                  </td>
                  <td>
                    <figure>";
          // Display image if question has a figure
          if (!empty($rec['questionFigureFileName'])) {
            echo "<img src='uploads/{$rec['questionFigureFileName']}' class='question-figure'>";
          }
          // Display question text and answers
          echo "<figcaption>" . htmlspecialchars($rec['question']) . "</figcaption>
                    </figure>
                    <p>A) " . htmlspecialchars($rec['answerA']) . "</p>
                    <p>B) " . htmlspecialchars($rec['answerB']) . "</p>
                    <p>C) " . htmlspecialchars($rec['answerC']) . "</p>
                    <p>D) " . htmlspecialchars($rec['answerD']) . "</p>
                    <p><strong>Correct Answer:</strong> " . htmlspecialchars($rec['correctAnswer']) . "</p>
                  </td>
                  <td>
                    <!-- Review form: educator can approve or disapprove the question -->
                    <form method='POST' action='review_recommendation.php' class='review-form'>
                      <!-- Hidden input holds the question ID -->
                      <input type='hidden' name='recommendID' value='{$rec['id']}'>
                      
                      <!-- Educator can write an optional comment -->
                      <textarea name='comment' placeholder='Write your comment...'></textarea><br>
                      
                      <!-- Approval options -->
                      <label><input type='radio' name='status' value='approved'> Approve</label>
                      <label><input type='radio' name='status' value='disapproved'> Disapprove</label><br>
                      
                      <!-- Submit button -->
                      <button type='submit'>Submit Review</button>
                    </form>
                  </td>
                </tr>";
        }
      } else {
        // If there are no pending recommended questions
        echo "<tr><td colspan='4'>No recommended questions pending review.</td></tr>";
      }
      ?>
    </table>
  </section>
</main>

<!-- ============================================================
     🔹 Footer Section
     ============================================================ -->
<footer>
        <div class="footer-left">
            <h4 >Contact Us</h4>
            <p>📞 +966 5555 12345</p>
            <p>📧 TechQuiz@example.com</p>
        </div>
      
    </footer>

</body>
</html>
