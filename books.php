<?php
session_start();
include "connect.php"; // your database connection

// Fetch all books
$stmt = $conn->query("SELECT * FROM books ORDER BY level ASC, category ASC, title ASC");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EDUKAMERE | Free Books</title>
<style>
:root{
  --green:#16a34a;
  --orange:#f97316;
  --bg:#f4f7fb;
  --text:#222;
}
body{margin:0;font-family: Arial,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
/* SIDEBAR */
.sidebar{position:fixed;top:0;left:-70px;width:70px;height:100%;background:var(--green);padding:10px 6px;transition:all 0.3s ease;z-index:5000;overflow:hidden;}
.sidebar.active{left:0;width:220px;}
.sidebar h3{color:var(--orange);text-align:center;font-size:14px;margin-bottom:15px;white-space:nowrap;}
.sidebar a{display:flex;align-items:center;gap:10px;padding:10px;color:#fff;text-decoration:none;font-weight:bold;border-radius:6px;font-size:14px;white-space:nowrap;}
.sidebar:not(.active) a span{display:none;}
.sidebar a:hover{background:rgba(255,255,255,0.2);}
/* OVERLAY */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:4000;}
.overlay.show{display:block;}
/* HEADER */
header{background:var(--green);color:#fff;padding:10px;position:sticky;top:0;z-index:3000;}
.header-top{display:flex;justify-content:space-between;align-items:center;}
.logo{color:var(--orange);font-weight:bold;}
.icon-btn{background:none;border:none;color:#fff;font-size:22px;cursor:pointer;}
/* MAIN CONTENT */
main{padding:20px;}
h2{color:var(--green);margin-bottom:15px;}
.category-heading{font-size:20px;color:var(--green);margin:25px 0 10px;border-bottom:2px solid var(--green);padding-bottom:6px;}
.book-section{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;}
.book-card{background:#fff;padding:12px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);text-align:center;transition:transform 0.2s;}
.book-card:hover{transform:scale(1.05);}
.book-card img{width:100%;height:140px;object-fit:cover;border-radius:8px;}
.book-card h4{color:var(--green);margin:10px 5px;font-size:15px;}
.book-card p{margin:5px 0;font-size:13px;color:#555;}
.book-card a{display:block;margin-top:8px;font-size:13px;color:var(--orange);text-decoration:none;}
footer{background:var(--green);color:#fff;text-align:center;font-size:12px;padding:12px;}
footer a{color:#fff;text-decoration:none;margin:0 5px;}
footer a:hover{color:var(--orange);}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <h3>EDUKAMERE</h3>
  <a href="past-questions.php">📘 <span>Past Questions</span></a>
  <a href="quiz.php">📝 <span>Quiz</span></a>
  <a href="courses.php">🎓 <span>Course</span></a>
  <a href="books.php">📚 <span>Free Books</span></a>
  <a href="settings.php">⚙️ <span>Settings</span></a>
  <a href="#">🚪 <span>Logout</span></a>
</div>
<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<header>
  <div class="header-top">
    <button class="icon-btn" onclick="toggleMenu()">☰</button>
    <span class="logo">EDUKAMERE</span>
  </div>
</header>

<main>
<h2>📚 Free Books for All Levels</h2>

<?php
$currentLevel = "";
foreach($books as $book):
    if($book['level'] != $currentLevel):
        if($currentLevel != "") echo "</div>"; // close previous section
        $currentLevel = $book['level'];
        echo "<div class='category-heading'>".htmlspecialchars($currentLevel)."</div>";
        echo "<div class='book-section'>";
    endif;
?>
<div class="book-card">
    <img src="https://via.placeholder.com/200x140.png?text=<?php echo urlencode($book['title']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
    <h4><?php echo htmlspecialchars($book['title']); ?></h4>
    <p>Category: <?php echo htmlspecialchars($book['category']); ?></p>
    <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
    <a href="<?php echo htmlspecialchars($book['file_path']); ?>" target="_blank">Download PDF</a>
</div>
<?php
endforeach;
if(!empty($books)) echo "</div>"; // close last section
?>

</main>

<footer>
  <div>Email: joelofficiel03@gmail.com</div>
  <div>
    <a href="#">Facebook</a> |
    <a href="#">LinkedIn</a> |
    <a href="#">GitHub</a> |
    <a href="#">YouTube</a> |
    <a href="#">Instagram</a>
  </div>
  <div>Location: Yaounde © <?php echo date("Y"); ?> EDUKAMERE</div>
</footer>

<script>
function toggleMenu(){
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  sidebar.classList.toggle("active");
  overlay.classList.toggle("show");
  document.body.style.overflow = sidebar.classList.contains("active") ? "hidden" : "auto";
}
</script>

</body>
</html>
