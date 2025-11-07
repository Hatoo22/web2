<?php
session_start();
include("db_connect.php");

// --------------------------------------------------
// CHECK USER LOGIN AND ROLE
// --------------------------------------------------
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'educator'){
    //header("Location: login.php?error=unauthorized");
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
  <style>
    body { font-family: Tahoma, Arial; background: #f9f9f9; }
    .container { border: 2px solid #000; background: #ffffff; padding: 20px; margin: 30px auto; width: 90%; }
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

<div class="container">
  <div class="headerQuiz">
    <h2>Quiz Questions:</h2>
    <a href="add_question.php?quizID=<?php echo $quizID; ?>">Add New Question</a>
  </div>

  <table>
    <tr>
      <th>Question</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>

    <?php
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
            echo "<td>";

            echo "<div>".$row['question']."</div><br>";

            if(!empty($row['questionFigureFileName'])){
                echo "<div><img src='uploads/".$row['questionFigureFileName']."' width='200'></div><br>";
            }

            echo "<div class='answers'>";
            echo "<div>A) ".($row['correctAnswer'] == 'A' ? "<span class='correct'>".$row['answerA']."</span>" : $row['answerA'])."</div>";
            echo "<div>B) ".($row['correctAnswer'] == 'B' ? "<span class='correct'>".$row['answerB']."</span>" : $row['answerB'])."</div>";
            echo "<div>C) ".($row['correctAnswer'] == 'C' ? "<span class='correct'>".$row['answerC']."</span>" : $row['answerC'])."</div>";
            echo "<div>D) ".($row['correctAnswer'] == 'D' ? "<span class='correct'>".$row['answerD']."</span>" : $row['answerD'])."</div>";
            echo "</div>";

            echo "</td>";

            echo "<td><a href='edit_question.php?id=".$row['id']."'>Edit</a></td>";
            echo "<td><a href='delete_question.php?quizID=$quizID&questionID=".$row['id']."' onclick=\"return confirm('Are you sure you want to delete this question?');\">Delete</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No questions yet for this quiz.</td></tr>";
    }

    mysqli_close($conn);
    ?>
  </table>
</div>

</body>
</html>
