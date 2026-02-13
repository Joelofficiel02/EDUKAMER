<?php
session_start();
include "connect.php";

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Check admin role


$success = "";
$error = "";

// Handle Upload
if(isset($_POST['upload'])){

    $level = trim($_POST['level']);
    $subject = trim($_POST['subject']);
    $year = intval($_POST['year']);
    $type = $_POST['type'];
    $price = intval($_POST['price']);

    if(isset($_FILES['pdf']) && $_FILES['pdf']['error'] == 0){

        $allowed = ['application/pdf'];

        if(!in_array($_FILES['pdf']['type'], $allowed)){
            $error = "Only PDF files are allowed.";
        } else {

            $folder = "uploads/";
            if(!is_dir($folder)){
                mkdir($folder,0777,true);
            }

            $filename = time()."_".preg_replace("/[^a-zA-Z0-9.]/","_",$_FILES['pdf']['name']);
            $filePath = $folder.$filename;

            if(move_uploaded_file($_FILES['pdf']['tmp_name'], $filePath)){

                $stmt = $conn->prepare("INSERT INTO past_questions 
                    (level, subject, year, type, file_path, price)
                    VALUES (:level,:subject,:year,:type,:file_path,:price)");

                $stmt->execute([
                    ':level'=>$level,
                    ':subject'=>$subject,
                    ':year'=>$year,
                    ':type'=>$type,
                    ':file_path'=>$filePath,
                    ':price'=>$price
                ]);

                $success = "Upload successful!";
            } else {
                $error = "File upload failed.";
            }
        }
    } else {
        $error = "Please select a PDF file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Upload | EDUKAMERE</title>

<style>
body{
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f6f9;
}

.container{
    max-width:600px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    margin-bottom:25px;
}

label{
    font-weight:bold;
    display:block;
    margin-bottom:5px;
}

input, select{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#007bff;
    color:white;
    font-size:16px;
    border-radius:6px;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#0056b3;
}

.success{
    background:#d4edda;
    color:#155724;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    text-align:center;
}

.error{
    background:#f8d7da;
    color:#721c24;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    text-align:center;
}

/* Mobile */
@media(max-width:600px){
    .container{
        margin:20px;
        padding:20px;
    }
}
</style>
</head>
<body>

<div class="container">
    <h2>Admin Upload Past Questions</h2>

    <?php if($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="uploadForm">

        <label>Level</label>
        <input type="text" name="level" required>

        <label>Subject</label>
        <input type="text" name="subject" required>

        <label>Year</label>
        <input type="number" name="year" min="2000" max="2030" required>

        <label>Type</label>
        <select name="type">
            <option value="question">Question</option>
            <option value="answer">Answer</option>
        </select>

        <label>Price (XAF)</label>
        <input type="number" name="price" value="0" min="0">

        <label>Upload PDF</label>
        <input type="file" name="pdf" accept="application/pdf" required>

        <button type="submit" name="upload">Upload</button>
    </form>
</div>

<script>
// Simple client validation
document.getElementById("uploadForm").addEventListener("submit", function(e){
    const fileInput = document.querySelector("input[name='pdf']");
    if(fileInput.files.length === 0){
        alert("Please select a PDF file.");
        e.preventDefault();
    }
});
</script>

</body>
</html>
