<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

include 'db_connect.php';

// متغيرات الأخطاء
$learnerError = "";
$educatorError = "";

// تحديد نوع الفورم الذي يجب عرضه بعد إعادة التحميل
$selectedType = $_POST['userType'] ?? "";

function uploadProfile($file){
    $default = "default.png"; 
    if(isset($file) && $file['error']==0){
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid() . "." . $ext;
        move_uploaded_file($file['tmp_name'], "image/".$newName);
        return $newName;
    }
    return $default;
}

if($_SERVER['REQUEST_METHOD'] === "POST"){
    $first = $_POST['firstName'] ?? '';
    $last  = $_POST['lastName'] ?? '';
    $email = $_POST['emailAddress'] ?? '';
    $passwordRaw = $_POST['password'] ?? '';
    $type  = $_POST['userType'] ?? '';
    $profile = uploadProfile($_FILES['photoFileName'] ?? null);
    $pass = password_hash($passwordRaw, PASSWORD_DEFAULT);

    // تحقق من وجود الإيميل
    $check = $conn->prepare("SELECT id FROM user WHERE emailAddress=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        if($type == "Learner") $learnerError = "This email is already registered!";
        if($type == "Educator") $educatorError = "This email is already registered!";
    } else {
        // إضافة المستخدم
        $stmt = $conn->prepare("INSERT INTO user (firstName,lastName,emailAddress,password,photoFileName,userType) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $first, $last, $email, $pass, $profile, $type);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_type'] = $type;

        // إذا Educator سجل المواضيع
        if($type=="Educator" && isset($_POST['check'])){
            foreach($_POST['check'] as $topicID){
                $stmt2 = $conn->prepare("INSERT INTO quiz (educatorID, topicID) VALUES (?,?)");
                $stmt2->bind_param("ii", $userId, $topicID);
                $stmt2->execute();
                $stmt2->close();
            }
        }

        // تحويل للصفحة المناسبة
        if($type=="Learner"){
            header("Location: LearnerHomepage.php");
        } else {
            header("Location: educator.php");
        }
        exit;
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up</title>
<style> 
*{ padding: 0; margin: 0; box-sizing: border-box; font-family:sans-serif }
body { background: #465a77; height: 100vh; display: flex; justify-content: center; align-items: flex-start; padding-top: 60px; }
.forms-L{ width: 350px; background: #fff; padding: 30px; border-radius: 15px; box-shadow: -2px 2px 8px #0000002f; }
.forms-E{ width: 350px; background: #fff; padding: 10px; border-radius: 15px; box-shadow: -2px 2px 8px #0000002f; }
.learn-or-edu{ width: 500px; background: #becffc; padding: 30px; border-radius: 15px; box-shadow: -2px 2px 8px #0000002f; }
.learn-or-edu, .forms-L, .forms-E { margin-top: 30px; }
h1 { color: #ffffff; font-size: 50px; text-align: center; margin-top: 10px; margin-bottom: 20px; }
h3{ padding-bottom: 20px; color: #0d688a; font-size: 150%; }
.forms-L .inp-L input, .forms-L .inp-L select, .forms-L .inp-L textarea, .forms-L .inp-L input[type="file"],
.forms-E .inp-E input, .forms-E .inp-E select, .forms-E .inp-E textarea, .forms-E .inp-E input[type="file"] { width: 100%; display: block; padding: 10px; border: 1px solid #bbb; border-radius: 8px; margin-bottom: 12px; }
label { display:block; margin:6px 0 4px; color:#000000; font-weight:600; }
button{ width: 100%; display: block; padding: 10px; border: 1px solid #bbb; border-radius: 8px; margin-bottom: 12px; font-size: 15px; }
.hidden { display: none !important; }
.text-optional{ font-size: 12px; padding-bottom: 20px; color: #bbb; }
.learn-or-edu { background-color: #98c4ec; display: flex; align-items: center; justify-content: center; gap: 70px; }
.checks{ display: grid; grid-template-columns: auto 1fr; gap: 5px 8px; align-items: center; padding: 10px; padding-left: 100px; }
button:hover { background: #03455e; color: #fff; }
label:hover{ color: rgb(38, 120, 170); }
.errorMsg{ color:red; font-weight:bold; margin-bottom:12px; font-size:13px; }
</style>
</head>
<body>
<div class="container">
<h1>Sign Up</h1>

<div class="learn-or-edu <?php echo ($selectedType!="") ? 'hidden' : ''; ?>">
<label><input type="radio" name="type" value="Learner"> Learner</label>
<label><input type="radio" name="type" value="Educator"> Educator</label>
</div>

<!-- Learner Form -->
<div class="forms-L <?php echo ($selectedType=="Learner") ? '' : 'hidden'; ?>">
<form method="POST" enctype="multipart/form-data">
<div class="inp-L">
    
<h3>Learner Information</h3>
<label>First Name:*</label>
<input type="text" name="firstName" placeholder="Enter your first name" value="<?php echo $_POST['firstName'] ?? ''; ?>" required>

<label>Last Name:*</label>
<input type="text" name="lastName" placeholder="Enter your last name" value="<?php echo $_POST['lastName'] ?? ''; ?>" required>

<label>Email Address:*</label>
<input type="email" name="emailAddress" placeholder="Enter your email" value="<?php echo $_POST['emailAddress'] ?? ''; ?>" required>

<?php if($learnerError) echo "<p class='errorMsg'>$learnerError</p>"; ?>

<label>Password:*</label>
<input type="password" name="password" placeholder="Enter your password" required>

<label>Profile Image:</label>
<input type="file" name="photoFileName" accept="image/*">
<p class="text-optional">Optional - A default image will be used if not provided</p>

<input type="hidden" name="userType" value="Learner">
<button type="submit">Sign up as Learner</button>
</div>
</form>
</div>

<!-- Educator Form -->
<div class="forms-E <?php echo ($selectedType=="Educator") ? '' : 'hidden'; ?>">
<form id="teacherForm" method="POST" enctype="multipart/form-data">
<div class="inp-E">
<h3>Educator Information</h3>
<label>First Name:*</label>
<input type="text" name="firstName" placeholder="Enter your first name" value="<?php echo $_POST['firstName'] ?? ''; ?>" required>

<label>Last Name:*</label>
<input type="text" name="lastName" placeholder="Enter your last name" value="<?php echo $_POST['lastName'] ?? ''; ?>" required>

<label>Email Address:*</label>
<input type="email" name="emailAddress" placeholder="Enter your email" value="<?php echo $_POST['emailAddress'] ?? ''; ?>" required>

<?php if($educatorError) echo "<p class='errorMsg'>$educatorError</p>"; ?>
<label>Password:*</label>
<input type="password" name="password" placeholder="Enter your password" required>
<label>Profile Image:</label>
<input type="file" name="photoFileName" accept="image/*">
<p class="text-optional">Optional - A default image will be used if not provided</p>
<input type="hidden" name="userType" value="Educator">

<label>Specializes:*</label>
<div class="checks">
<input type="checkbox" id="check1" name="check[]" value="1" <?php echo (isset($_POST['check']) && in_array(1,$_POST['check'])) ? 'checked' : ''; ?>><label for="check1">Computer Networks</label>
<input type="checkbox" id="check2" name="check[]" value="2" <?php echo (isset($_POST['check']) && in_array(2,$_POST['check'])) ? 'checked' : ''; ?>><label for="check2">Database Systems</label>
<input type="checkbox" id="check3" name="check[]" value="3" <?php echo (isset($_POST['check']) && in_array(3,$_POST['check'])) ? 'checked' : ''; ?>><label for="check3">Web Development</label>
</div>
<p id="specialError" class="errorMsg" style="display:none;">Please select at least one specialization.</p>

<button type="submit">Sign up as Educator</button>
</div>
</form>
</div>

</div>

<script>
const chooser = document.querySelector('.learn-or-edu');
const studentCard = document.querySelector('.forms-L');
const teacherCard = document.querySelector('.forms-E');

document.querySelectorAll('.learn-or-edu input[name="type"]').forEach(rb=>{
    rb.addEventListener('change', ()=>{
        chooser.classList.add('hidden');
        if(rb.value==="Learner"){
            studentCard.classList.remove('hidden');
            teacherCard.classList.add('hidden');
        } else {
            teacherCard.classList.remove('hidden');
            studentCard.classList.add('hidden');
        }
    });
});

// التحقق من اختيار التخصصات
document.getElementById('teacherForm').addEventListener('submit', function(e){
    const checks = document.querySelectorAll('input[name="check[]"]');
    let checkedOne = false;
    checks.forEach(chk => { if(chk.checked) checkedOne = true; });
    const errorMsg = document.getElementById('specialError');
    if(!checkedOne){
        e.preventDefault();
        errorMsg.style.display = "block";
    } else {
        errorMsg.style.display = "none";
    }
});
</script>
</body>
</html>
