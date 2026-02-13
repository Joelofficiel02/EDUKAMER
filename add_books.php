<?php
session_start();
include "connect.php"; // your PDO database connection

// Handle book upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $title = $_POST['title'] ?? '';
    $level = $_POST['level'] ?? '';
    $category = $_POST['category'] ?? '';
    $author = $_POST['author'] ?? '';

    if(isset($_FILES['book_file']) && $_FILES['book_file']['error'] === 0){
        $fileTmp = $_FILES['book_file']['tmp_name'];
        $fileName = basename($_FILES['book_file']['name']);
        $uploadDir = "uploads/books/";
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filePath = $uploadDir . time() . "_" . $fileName;

        if(move_uploaded_file($fileTmp, $filePath)){
            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO books (title, level, category, author, file_path) VALUES (:title, :level, :category, :author, :file_path)");
            $stmt->execute([
                ':title'=>$title,
                ':level'=>$level,
                ':category'=>$category,
                ':author'=>$author,
                ':file_path'=>$filePath
            ]);
            $success = "Book uploaded successfully!";
        } else {
            $error = "Failed to upload file.";
        }
    } else {
        $error = "Please select a PDF file.";
    }
}

// Handle deletion
if(isset($_GET['delete'])){
    $bookId = (int)$_GET['delete'];

    // Fetch file path
    $stmt = $conn->prepare("SELECT file_path FROM books WHERE id = :id");
    $stmt->execute([':id'=>$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if($book){
        if(file_exists($book['file_path'])){
            unlink($book['file_path']); // delete file
        }
        // Delete from DB
        $stmtDel = $conn->prepare("DELETE FROM books WHERE id = :id");
        $stmtDel->execute([':id'=>$bookId]);
        $success = "Book deleted successfully!";
    } else {
        $error = "Book not found.";
    }
}

// Fetch all books for display
$books = $conn->query("SELECT * FROM books ORDER BY level, title ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EDUKAMERE | Admin Upload Books</title>
<style>
:root{
  --green:#16a34a;
  --orange:#f97316;
  --bg:#f4f7fb;
  --text:#222;
}
body{margin:0;font-family:Arial,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
header{background:var(--green);color:#fff;padding:10px;position:sticky;top:0;z-index:3000;text-align:center;}
header h1{margin:0;}
main{padding:20px;max-width:800px;margin:auto;}
form{background:#fff;padding:20px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);display:grid;gap:12px;margin-bottom:20px;}
input, select{padding:10px;border-radius:8px;border:1px solid #ccc;width:100%;}
button{padding:10px;background:var(--green);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:bold;}
.success{color:green;margin-bottom:10px;}
.error{color:red;margin-bottom:10px;}
.book-table{width:100%;border-collapse:collapse;}
.book-table th, .book-table td{padding:10px;border-bottom:1px solid #ccc;text-align:left;}
.book-table th{background:var(--green);color:#fff;}
.delete-btn{background:red;padding:4px 8px;border:none;color:#fff;border-radius:6px;cursor:pointer;}
@media(max-width:600px){
    .book-table th, .book-table td{font-size:13px;padding:6px;}
    button{font-size:14px;padding:8px;}
}
</style>
</head>
<body>

<header>
    <h1>Admin Panel – Upload & Manage Books</h1>
</header>

<main>

<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>
<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

<h2>📤 Upload a Book</h2>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="upload">
    <label>Level:</label>
    <select name="level" required>
        <option value="">Select Level</option>
        <option value="FSLC">FSLC</option>
        <option value="GCE">GCE</option>
        <option value="GCE OL">GCE OL</option>
        <option value="GCE AL">GCE AL</option>
        <option value="HND">HND</option>
    </select>

    <input type="text" name="title" placeholder="Book Title" required>
    <input type="text" name="category" placeholder="Category (Science, Arts, Fiction)" required>
    <input type="text" name="author" placeholder="Author" required>
    <input type="file" name="book_file" accept=".pdf" required>
    <button type="submit">Upload Book</button>
</form>

<h2>📚 Existing Books</h2>
<table class="book-table">
    <tr>
        <th>ID</th>
        <th>Level</th>
        <th>Title</th>
        <th>Category</th>
        <th>Author</th>
        <th>PDF</th>
        <th>Action</th>
    </tr>
    <?php foreach($books as $b): ?>
    <tr>
        <td><?= $b['id'] ?></td>
        <td><?= htmlspecialchars($b['level']) ?></td>
        <td><?= htmlspecialchars($b['title']) ?></td>
        <td><?= htmlspecialchars($b['category']) ?></td>
        <td><?= htmlspecialchars($b['author']) ?></td>
        <td><a href="<?= htmlspecialchars($b['file_path']) ?>" target="_blank">View PDF</a></td>
        <td>
            <a class="delete-btn" href="?delete=<?= $b['id'] ?>" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</main>
</body>
</html>
