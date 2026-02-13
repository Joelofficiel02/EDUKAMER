<?php
session_start();
include "connect.php"; // your PDO connection

// Create folders if missing
if(!is_dir('uploads/courses')) mkdir('uploads/courses', 0777, true);
if(!is_dir('uploads/course_images')) mkdir('uploads/course_images', 0777, true);

// Handle create / edit / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Delete
    if(isset($_POST['delete_id'])){
        $id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("SELECT file_path, image FROM courses WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            if($row['file_path'] && file_exists($row['file_path'])) unlink($row['file_path']);
            if($row['image'] && file_exists($row['image'])) unlink($row['image']);
        }
        $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
        $stmt->execute([$id]);
    }

    // Create / Edit
    if(isset($_POST['save_course'])){
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $level = $_POST['level'];
        $course_name = $_POST['course_name'];
        $approach = $_POST['approach'];
        $author = $_POST['author'];
        $price = floatval($_POST['price']);

        // File upload
        $file_path = null;
        if(isset($_FILES['file']) && $_FILES['file']['tmp_name']){
            $filename = time().'_'.basename($_FILES['file']['name']);
            $file_path = 'uploads/courses/'.$filename;
            move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
        }

        // Image upload
        $image_path = null;
        if(isset($_FILES['image']) && $_FILES['image']['tmp_name']){
            $img_name = time().'_'.basename($_FILES['image']['name']);
            $image_path = 'uploads/course_images/'.$img_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }

        if($id){ // Edit
            $stmt = $conn->prepare("UPDATE courses SET level=?, course_name=?, approach=?, author=?, price=? ".($file_path?", file_path=?":"").($image_path?", image=?":"")." WHERE id=?");
            $params = [$level,$course_name,$approach,$author,$price];
            if($file_path) $params[] = $file_path;
            if($image_path) $params[] = $image_path;
            $params[] = $id;
            $stmt->execute($params);
        } else { // Create
            $stmt = $conn->prepare("INSERT INTO courses (level, course_name, approach, author, price, image, file_path) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$level,$course_name,$approach,$author,$price,$image_path,$file_path]);
        }
    }
}

// Fetch all courses
$courses = $conn->query("SELECT * FROM courses ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Courses</title>
<style>
:root{
  --green:#16a34a;
  --orange:#f97316;
  --bg:#f4f7fb;
  --text:#222;
}
body{margin:0;font-family:Arial,sans-serif;background:var(--bg);color:var(--text);}
header{background:var(--green);color:#fff;padding:10px;text-align:center;font-weight:bold;}
main{padding:20px;}
form{display:grid;gap:10px;background:#fff;padding:15px;border-radius:8px;margin-bottom:20px;box-shadow:0 2px 6px rgba(0,0,0,.1);}
input,select{padding:8px;border-radius:5px;border:1px solid #ccc;width:100%;}
button{padding:8px 12px;border:none;border-radius:6px;background:var(--green);color:#fff;cursor:pointer;}
button.delete{background:red;}
table{width:100%;border-collapse:collapse;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,.1);}
th,td{padding:8px;text-align:left;border-bottom:1px solid #ccc;}
img{max-width:80px;border-radius:4px;}
@media(max-width:600px){table,thead,tbody,th,td,tr{display:block;}td{margin-bottom:8px;}}
</style>
</head>
<body>

<header>Admin | Manage Courses</header>
<main>

<h3>Add / Edit Course</h3>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" id="course_id">
    <input type="text" name="level" placeholder="Level (FSLC, GCE OL, HND, etc)" required>
    <input type="text" name="course_name" placeholder="Course Name" required>
    <input type="text" name="approach" placeholder="Approach (Exam, Theory, Practical)" required>
    <input type="text" name="author" placeholder="Author Name">
    <input type="number" step="0.01" name="price" placeholder="Price (optional)">
    <input type="file" name="image" accept="image/*">
    <input type="file" name="file" accept=".pdf,.txt,.ppt,.pptx">
    <button type="submit" name="save_course">Save Course</button>
</form>

<h3>Existing Courses</h3>
<table>
<tr>
<th>Level</th><th>Course</th><th>Approach</th><th>Author</th><th>Price</th><th>Image</th><th>File</th><th>Actions</th>
</tr>
<?php foreach($courses as $c): ?>
<tr>
<td><?=htmlspecialchars($c['level'])?></td>
<td><?=htmlspecialchars($c['course_name'])?></td>
<td><?=htmlspecialchars($c['approach'])?></td>
<td><?=htmlspecialchars($c['author'])?></td>
<td><?=number_format($c['price'],2)?></td>
<td><?php if($c['image']): ?><img src="<?=$c['image']?>" alt="img"><?php endif;?></td>
<td><?php if($c['file_path']): ?><a href="<?=$c['file_path']?>" target="_blank">Download</a><?php endif;?></td>
<td>
<form method="post" style="display:inline;">
    <input type="hidden" name="delete_id" value="<?=$c['id']?>">
    <button type="submit" class="delete" onclick="return confirm('Delete this course?')">Delete</button>
</form>
<button onclick="editCourse(<?=htmlspecialchars(json_encode($c))?>)">Edit</button>
</td>
</tr>
<?php endforeach; ?>
</table>

</main>

<script>
function editCourse(course){
    document.querySelector('input[name="id"]').value = course.id;
    document.querySelector('input[name="level"]').value = course.level;
    document.querySelector('input[name="course_name"]').value = course.course_name;
    document.querySelector('input[name="approach"]').value = course.approach;
    document.querySelector('input[name="author"]').value = course.author;
    document.querySelector('input[name="price"]').value = course.price;
}
</script>

</body>
</html>
