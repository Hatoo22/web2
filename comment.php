<?php
include("db_connect.php"); // ملف الاتصال بقاعدة البيانات


$quizID = isset($_GET['quizID']) ? intval($_GET['quizID']) : 0;

$sql = "SELECT * FROM quizfeedback WHERE quizID = $quizID ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment page</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
  min-height: 100vh; /* ارتفاع كامل الصفحة */
  display: flex;
  flex-direction: column;
}
        .bar {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 14px 0;
        }
        .brand {
          color: #071b45;
          font-weight: 800;
          font-size: 22px;
          letter-spacing: .3px;
        }
        .com1{
          border:2px solid #b7b7b7;
          border-radius:12px;
          box-shadow:0 6px 2px rgba(0,0,0,.06);
          padding:20px;
          margin:24px auto;
          max-width:900px;  
        }
        .Comments{
            padding: 10px;
            font-size: 20px;
        }
        .rating{
            text-align: right;
            font-size: 20px;
            font-weight: bolder;
        }
        .com1 .profile{
            float:left; 
            margin-inline-end:10px;
        }
        .com1 h3{ 
            display:inline-block;
            margin:0;
            line-height:50px;
        }
        
        footer {
  margin-top: auto; 
  
}
    </style>
</head>

<body>
<header>
    <div class="logo">
      <img src="image/logo.png" alt="logo">
      <span>TechQuiz</span>
    </div>

    <div class="navbar">
      <a href="educator.html">Home</a>
    </div>

    <div class="logout">
      <a href="logout.php">Log out</a>
    </div>
</header>

<?php
// ✅ عرض التعليقات المسترجعة من قاعدة البيانات
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="com1">';
        echo '<img src="image/teacher1.jpg" alt="comment-profile" class="profile" height="50">';
        echo '<h3>Anonymous</h3>';  // الاسم أصبح مجهول
        echo '<p class="rating">' . htmlspecialchars($row["rating"]) . '/5</p>'; // التقييم
        echo '<p class="Comments">' . htmlspecialchars($row["comments"]) . '</p>'; // التعليق
        // عرض التاريخ
        echo '<p style="font-size:14px; color:gray;">' . date("d-m-Y H:i", strtotime($row["date"])) . '</p>';
        echo '</div>';
    }
} else {
    echo "<p style='text-align:center; font-size:18px;'>No comments found for this quiz.</p>";
}
?>


<footer>
    <div class="footer-left">
        <h4>Contact Us</h4>
        <p>📞 +966 5555 12345</p>
        <p>📧 TechQuiz@example.com</p>
    </div>
</footer>
</body>
</html>
