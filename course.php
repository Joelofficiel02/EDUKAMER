<?php
session_start();
include "connect.php"; // your PDO connection

// Fetch filters
$levels = $conn->query("SELECT DISTINCT level FROM courses ORDER BY level ASC")->fetchAll(PDO::FETCH_COLUMN);
$coursesList = $conn->query("SELECT DISTINCT course_name FROM courses ORDER BY course_name ASC")->fetchAll(PDO::FETCH_COLUMN);
$approaches = $conn->query("SELECT DISTINCT approach FROM courses ORDER BY approach ASC")->fetchAll(PDO::FETCH_COLUMN);

// Fetch all courses
$courses = $conn->query("SELECT * FROM courses ORDER BY level ASC, course_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EDUKAMERE | Courses</title>

<style>
:root{
  --green:#16a34a;
  --orange:#f97316;
  --bg:#f4f7fb;
  --text:#222;
}
body{
  margin:0;
  font-family: Arial, sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  overflow-x:hidden;
}

/* SIDEBAR */
.sidebar{
  position:fixed;
  top:0; left:-70px;
  width:70px; height:100%;
  background:var(--green);
  padding:10px 6px;
  transition:all 0.3s ease;
  z-index:5000;
  overflow:hidden;
}
.sidebar.active{ left:0; width:220px; }
.sidebar h3{ color:var(--orange); text-align:center; font-size:14px; margin-bottom:15px; white-space:nowrap; }
.sidebar a{
  display:flex; align-items:center; gap:10px;
  padding:10px; color:#fff; text-decoration:none;
  font-weight:bold; border-radius:6px; font-size:14px; white-space:nowrap;
}
.sidebar:not(.active) a span{ display:none; }
.sidebar a:hover{ background:rgba(255,255,255,0.2); }

/* OVERLAY */
.overlay{
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,0.45); z-index:4000;
}
.overlay.show{ display:block; }

/* HEADER */
header{
  background:var(--green);
  color:#fff;
  padding:10px;
  position:sticky;
  top:0;
  z-index:3000;
}
.header-top{
  display:flex; justify-content:space-between; align-items:center;
}
.logo{ color:var(--orange); font-weight:bold; }
.icon-btn{
  background:none; border:none; color:#fff;
  font-size:22px; cursor:pointer;
}

/* MAIN CONTENT */
main{ padding:20px; }
h2{ color:var(--green); margin-bottom:15px; }

/* FILTER BAR */
.filter-bar{
  display:flex; gap:15px;
  flex-wrap:wrap;
  margin-bottom:25px;
}
.filter-bar select{
  padding:8px 12px;
  border-radius:8px;
  border:1px solid #ccc;
  outline:none;
}

/* COURSE CARDS */
.course-section{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:18px;
}
.course-card{
  background:#fff;
  padding:12px;
  border-radius:10px;
  box-shadow:0 2px 6px rgba(0,0,0,0.1);
  text-align:center;
  transition:transform 0.2s;
}
.course-card:hover{ transform:scale(1.05); }
.course-card img{
  width:100%; height:140px; object-fit:cover; border-radius:8px;
}
.course-card h4{
  color:var(--green);
  margin:10px 5px;
  font-size:15px;
}
.course-card p{
  font-size:13px; margin:4px 0;
}
.course-card a{
  display:block;
  margin-top:8px;
  font-size:13px;
  color:var(--orange);
  text-decoration:none;
}

/* FOOTER */
footer{
  background:var(--green);
  color:#fff;
  text-align:center;
  font-size:12px;
  padding:12px;
  position:sticky;
  bottom:0;
  width:100%;
}
footer a{
  color:#fff; text-decoration:none; margin:0 5px;
}
footer a:hover{ color:var(--orange); }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <h3>EDUKAMERE</h3>
  <a href="past-questions.php">📘 <span>Past Questions</span></a>
  <a href="quiz.php">📝 <span>Quiz</span></a>
  <a href="course.php">🎓 <span>Course</span></a>
  <a href="free-books.php">📚 <span>Free Books</span></a>
  <a href="settings.php">⚙️ <span>Settings</span></a>
  <a href="logout.php">🚪 <span>Logout</span></a>
</div>
<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<header>
  <div class="header-top">
    <button class="icon-btn" onclick="toggleMenu()">☰</button>
    <span class="logo">EDUKAMERE</span>
  </div>
</header>

<main>
  <h2>🎓 Courses</h2>

  <div class="filter-bar">
    <select id="levelFilter">
      <option value="">Select Level</option>
      <?php foreach($levels as $lvl): ?>
        <option value="<?=htmlspecialchars($lvl)?>"><?=htmlspecialchars($lvl)?></option>
      <?php endforeach; ?>
    </select>

    <select id="courseFilter">
      <option value="">Select Course</option>
      <?php foreach($coursesList as $c): ?>
        <option value="<?=htmlspecialchars($c)?>"><?=htmlspecialchars($c)?></option>
      <?php endforeach; ?>
    </select>

    <select id="approachFilter">
      <option value="">Select Approach</option>
      <?php foreach($approaches as $a): ?>
        <option value="<?=htmlspecialchars($a)?>"><?=htmlspecialchars($a)?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="course-section" id="courseSection">
  <?php foreach($courses as $c): ?>
    <div class="course-card" 
         data-level="<?=htmlspecialchars($c['level'] ?? '')?>"
         data-course="<?=htmlspecialchars($c['course_name'] ?? '')?>"
         data-approach="<?=htmlspecialchars($c['approach'] ?? '')?>">

      <img src="<?=!empty($c['image']) ? htmlspecialchars($c['image']) : 'https://via.placeholder.com/200x140.png?text=No+Image'?>" 
           alt="<?=htmlspecialchars($c['course_name'] ?? 'No Name')?>">

      <h4><?=htmlspecialchars($c['course_name'] ?? 'Unknown')?> (<?=htmlspecialchars($c['level'] ?? '')?>)</h4>
      <p>Approach: <?=htmlspecialchars($c['approach'] ?? 'N/A')?></p>
      <p>Author: <?=htmlspecialchars($c['author'] ?? 'Unknown')?></p>
      <p>Price: <?=number_format($c['price'] ?? 0,2)?> XAF</p>

      <?php if(!empty($c['file_path'])): ?>
        <a href="<?=htmlspecialchars($c['file_path'])?>" target="_blank">Download File</a>
      <?php else: ?>
        <span style="color:red;font-size:12px;">No file uploaded</span>
      <?php endif; ?>

    </div>
  <?php endforeach; ?>
  </div>
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
  <div>Location: Yaounde © <?=date("Y")?> EDUKAMERE</div>
</footer>

<script>
function toggleMenu(){
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  sidebar.classList.toggle("active");
  overlay.classList.toggle("show");
  document.body.style.overflow = sidebar.classList.contains("active") ? "hidden" : "auto";
}

// Filter functionality
const levelEl = document.getElementById("levelFilter");
const courseEl = document.getElementById("courseFilter");
const approachEl = document.getElementById("approachFilter");
const courseCards = document.querySelectorAll(".course-card");

function filterCourses(){
  const level = levelEl.value.toLowerCase();
  const course = courseEl.value.toLowerCase();
  const approach = approachEl.value.toLowerCase();

  courseCards.forEach(card=>{
    const cLevel = card.dataset.level.toLowerCase();
    const cCourse = card.dataset.course.toLowerCase();
    const cApproach = card.dataset.approach.toLowerCase();

    if((!level || cLevel.includes(level)) &&
       (!course || cCourse.includes(course)) &&
       (!approach || cApproach.includes(approach))){
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

levelEl.addEventListener("change", filterCourses);
courseEl.addEventListener("change", filterCourses);
approachEl.addEventListener("change", filterCourses);
</script>

</body>
</html>
