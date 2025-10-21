<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
  <title>log in </title>
      <link rel="stylesheet" href="style.css">
<style>
body {
  font-family: Arial, sans-serif;
  margin: 0; padding: 0;
  background: #f9f9f9;
  color: #333;
}



main {
  padding: 20px;
}

.form-box {
  background: #fff;
  padding: 20px;
  width: 300px;
  margin: auto;
  border-radius: 5px;
  box-shadow: 0 0 5px #ccc;
}

.form-box input, .form-box button, textarea {
  display: block;
  width: 100%;
  margin: 10px 0;
  padding: 8px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin: 15px 0;
}

table, th, td {
  border: 1px solid #ddd;
  padding: 10px;
}

th {
  background: #f0f0f0;
}

.profile-pic {
  width: 100px;
  border-radius: 50%;
}

.profile-mini {
  width: 30px;
  border-radius: 50%;
}

.logo {
  width: 80px;   
  height: auto;
  margin-right: 15px;
  vertical-align: middle;
}
header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.topics ul {
  list-style: none;
  padding: 0;
}

.topics li {
  display: flex;
  align-items: center;
  margin: 12px 0;
  font-size: 18px;
}

.topics li img {
  width: 50px;
  height: 50px;
  margin-right: 12px;
}

.profile-pic {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  margin-bottom: 10px;
  display: block;
}

.user-info {
  margin-left: 15px;
  margin-bottom: 30px;
}


.learner-pic {
  width: 50px;
  height: 50px;
  border-radius: 50%;
}

.question-figure {
  width: 150px;
  display: block;
  margin: 10px auto;
}

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

.review-form button:hover {
  background: #0066cc;
}
</style>

  <meta charset="UTF-8">
  
</head>
<body>
   <header>
    <div class="logo">
      <img src="image/logo.png" alt="logo">
      <span>TechQuiz</span>
    </div>

    

     <div class="logout">
    <a href="index.html">Log out</a>
  </div>
  
  </header>

  <main>
<form class="form-box" id="loginForm" action="login1.php" method="POST">
   <?php
if (isset($_GET['error'])) {
    echo '<p style="color:red;">Invalid login ,Please try again.</p>';
}
?>
      <label>Email:</label>
    <input type="email" name="email" placeholder="Email">
      
      <label>Password:</label>
    <input type="password" name="password" placeholder="Password">
      
      <label>Sign in as:</label>
<select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="educator">Educator</option>
        <option value="learner">Learner</option>
      </select>
      
      <button type="submit">Login</button>
    </form>
      
      
  
  </main>

 


 <footer>
        <div class="footer-left">
            <h4 >Contact Us</h4>
            <p>📞 +966 5555 12345</p>
            <p>📧 TechQuiz@example.com</p>
        </div>
      
    </footer>
</body>
</html>

