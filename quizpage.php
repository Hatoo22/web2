<?php
session_start();

// --------------------------------------------------
// DATABASE CONNECTION
// --------------------------------------------------
// Use the same database connection as login.php
include("db_connect.php"); // This file should define $conn

// --------------------------------------------------
// CHECK USER LOGIN AND ROLE
// --------------------------------------------------
// Make sure the user is logged in and is an educator
// NOTE: using 'user_type' to match login.php session variable
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'educator'){
    //header("Location: login.php?error=unauthorized");
    exit();
}

// --------------------------------------------------
// DELETE QUESTION SECTION
// --------------------------------------------------
// Deletes the question and its figure if the delete link is clicked
if(isset($_GET['delete'])){
    $questionID = intval($_GET['delete']); 
    $quizID = intval($_GET['id']); 

    // Get the image filename before deleting the question
    $getFile = mysqli_query($conn, "SELECT questionFigureFileName FROM QuizQuestion WHERE id = $questionID");
    if($getFile && mysqli_num_rows($getFile) > 0){
        $fileRow = mysqli_fetch_assoc($getFile);
        $fileName = $fileRow['questionFigureFileName'];

        // Delete the image file if it exists
        if(!empty($fileName) && file_exists("uploads/" . $fileName)){
            unlink("uploads/" . $fileName);
        }
    }

    // Delete the question from the database
    mysqli_query($conn, "DELETE FROM QuizQuestion WHERE id = $questionID");

    // Redirect back to the quiz page
    header("Location: quiz.php?id=$quizID");
    exit();
}

// --------------------------------------------------
// RETRIEVE QUIZ ID FROM REQUEST
// --------------------------------------------------
$quizID = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($quizID <= 0){
    echo "<p>No quiz selected.</p>";
    exit();
}

// --------------------------------------------------
// RETRIEVE ALL QUESTIONS FOR THIS QUIZ
// --------------------------------------------------
$query = "SELECT * FROM QuizQuestion WHERE quizID = $quizID";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quiz Page</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { font-family: Tahoma, Arial; background: #f9f9f9; }
    .container { border: 2px solid #000; background: #ffffff; padding: 20px; }
    .headerQuiz { display: flex; justify-content: space-between; align-items: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 12px; vertical-align: top; }
    th { background: #91a9dd; text-align: center; }
    .answers div { margin: 3px 0; }
    .correct { color: blue; font-weight: bold; text-decoration: underline; }
    a { color: #007bff; text-decoration: none; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="image/logo.png" alt="logo">
    <span>TechQuiz</span>
  </div>
  <div class="navbar">
    <a href="educator_home.php">Home</a>
  </div>
  <div class="logout">
    <a href="logout.php">Log out</a>
  </div>
</header>

<div class="container">
  <div class="headerQuiz">
    <h2>Quiz Questions:</h2>
    <!-- Link to add a new question -->
    <a href="add_question.php?quizID=<?php echo $quizID; ?>">Add New Question</a>
  </div>

  <table>
    <tr>
      <th>Question</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>

    <?php
    // Check if there are questions for this quiz
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
            echo "<td>";

            // Display the question text
            echo "<div>".$row['question']."</div><br>";

            // Display image only if it exists
            if(!empty($row['questionFigureFileName'])){
                echo "<div><img src='uploads/".$row['questionFigureFileName']."' width='200'></div><br>";
            }

            // Display all answers and highlight the correct one
            echo "<div class='answers'>";
            echo "<div>A) ".($row['correctAnswer'] == 'A' ? "<span class='correct'>".$row['answerA']."</span>" : $row['answerA'])."</div>";
            echo "<div>B) ".($row['correctAnswer'] == 'B' ? "<span class='correct'>".$row['answerB']."</span>" : $row['answerB'])."</div>";
            echo "<div>C) ".($row['correctAnswer'] == 'C' ? "<span class='correct'>".$row['answerC']."</span>" : $row['answerC'])."</div>";
            echo "<div>D) ".($row['correctAnswer'] == 'D' ? "<span class='correct'>".$row['answerD']."</span>" : $row['answerD'])."</div>";
            echo "</div>";

            echo "</td>";

            // Edit and Delete links
            echo "<td><a href='edit_question.php?id=".$row['id']."'>Edit</a></td>";
            echo "<td><a href='quiz.php?id=$quizID&delete=".$row['id']."' onclick=\"return confirm('Are you sure you want to delete this question?');\">Delete</a></td>";
            echo "</tr>";
        }
    } else {
        // Display message if no questions exist
        echo "<tr><td colspan='3'>No questions yet for this quiz.</td></tr>";
    }

    // Close DB connection
    mysqli_close($conn);
    ?>
  </table>
</div>

<footer>
  <div class="footer-left">
    <h4>Contact Us</h4>
    <p>📞 +966 5555 12345</p>
    <p>📧 TechQuiz@example.com</p>
  </div>
</footer>

</body>
</html>
