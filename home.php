<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

/* Fetch user */
$stmt = $conn->prepare("SELECT first_name, dob FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$first_name = $user['first_name'];

/* Balance Logic: 0.X (X = age) */
$dob = new DateTime($user['dob']);
$today = new DateTime();
$age = $today->diff($dob)->y;
$balance = "0." . $age . " XCFA";

/* Fetch sections and cards dynamically */
$sections = $conn->query("SELECT * FROM sections ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home</title>

<style>
:root{
  --green:#16a34a;
  --orange:#f97316;
  --bg:#f4f7fb;
  --text:#1f2937;
  --card:#ffffff;
  --radius:16px;
}

*{
  box-sizing:border-box;
  margin:0;
  padding:0;
  -webkit-tap-highlight-color:transparent;
}

html,body{
  width:100%;
  max-width:100%;
  overflow-x:hidden;
}

body{
  font-family:"Segoe UI", system-ui, sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  display:flex;
  flex-direction:column;
}

/* HEADER */
header{
  backdrop-filter:blur(12px);
  background:rgba(22,163,74,.97);
  color:#fff;
  padding:12px 14px;
  position:sticky;
  top:0;
  z-index:3000;
  width:100%;
}

.header-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
}

.logo{
  font-size:20px;
  font-weight:800;
}

.right-icons{
  display:flex;
  gap:6px;
}

.icon-btn{
  background:rgba(255,255,255,.15);
  border:none;
  color:#fff;
  font-size:12px;
  cursor:pointer;
  padding:6px 10px;
  border-radius:10px;
  white-space:nowrap;
  min-height:34px;
}

/* CENTER LINKS */
.center-links{
  display:flex;
  justify-content:center;
  gap:16px;
  margin:10px 0;
  flex-wrap:wrap;
}

.center-links a{
  color:#e5e7eb;
  text-decoration:none;
  font-size:13px;
}

/* SEARCH */
.search-bar{
  width:100%;
}

.search-bar input{
  width:100%;
  padding:12px 14px;
  border:none;
  border-radius:12px;
  margin-top:6px;
  outline:none;
  font-size:14px;
}

/* HERO */
.hero{
  background:linear-gradient(135deg,var(--green),var(--orange));
  color:#fff;
  text-align:center;
  padding:35px 18px;
  margin:14px;
  border-radius:var(--radius);
}

.hero h1{
  font-size:22px;
  line-height:1.3;
}

.hero p{
  margin-top:10px;
  font-size:14px;
  opacity:.95;
}

/* MAIN */
main{
  flex:1;
  padding:12px 14px 30px;
  width:100%;
}

.topic-link{
  font-size:17px;
  font-weight:800;
  margin:22px 0 12px;
  border-left:5px solid var(--green);
  padding-left:10px;
}

/* SMART RESPONSIVE GRID */
.section{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
  gap:14px;
}

/* CARD */
.card{
  background:var(--card);
  border-radius:var(--radius);
  overflow:hidden;
  cursor:pointer;
  box-shadow:0 6px 15px rgba(0,0,0,.08);
  transition:.2s ease;
}

.card:active{
  transform:scale(.97);
}

.card img{
  width:100%;
  height:120px;
  object-fit:cover;
}

.card h4{
  color:var(--green);
  margin:12px 8px 14px;
  text-align:center;
  font-size:13px;
}

/* FOOTER */
footer{
  background:#020617;
  color:#cbd5f5;
  text-align:center;
  font-size:12px;
  padding:16px;
}

/* SIDEBAR */
.sidebar-overlay{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.6);
  z-index:3500;
}

.sidebar{
  position:fixed;
  top:0;
  right:-260px;
  width:240px;
  height:100%;
  background:#020617;
  padding:20px;
  transition:0.3s ease;
  z-index:3600;
  overflow-y:auto;
}

.sidebar.active{
  right:0;
}

.sidebar a{
  display:block;
  padding:12px;
  margin:10px 0;
  color:#fff;
  text-decoration:none;
  background:rgba(255,255,255,0.08);
  border-radius:10px;
  font-size:14px;
}

/* SMALL DEVICES */
@media (max-width:420px){

  .header-top{
    flex-direction:column;
    align-items:flex-start;
    gap:6px;
  }

  .right-icons{
    width:100%;
    justify-content:space-between;
  }

  .icon-btn{
    font-size:11px;
    padding:6px 8px;
  }

  .hero{
    margin:10px;
    padding:28px 14px;
  }

  .section{
    grid-template-columns:1fr;
  }

}
</style>


</head>

<body>

<header>
  <div class="header-top">
    <span class="logo">EDUKAMERE</span>
    <div class="right-icons">
      <button class="icon-btn">👤 <?= htmlspecialchars($first_name) ?></button>
      <button class="icon-btn">💰 <?= $balance ?></button>
      <button class="icon-btn" onclick="toggleSidebar()">☰</button>
    </div>
  </div>

  <div class="center-links">
    <a href="#">About</a>
    <a href="#">News</a>
    <a href="#">Message</a>
  </div>

  <div class="search-bar">
    <input type="text" placeholder="Search schools, courses, students...">
  </div>
</header>

<div class="hero">
  <h1>Welcome to EDUKAMERE</h1>
  <p>Cameroon’s digital hub for learning, excellence, and opportunity</p>
</div>

<main>
<?php foreach($sections as $sec): ?>
    <div class="topic-link"><?= htmlspecialchars($sec['icon'].' '.$sec['title']) ?></div>
    <div class="section">
    <?php
        $cards = $conn->prepare("SELECT * FROM cards WHERE section_id=? ORDER BY sort_order ASC");
        $cards->execute([$sec['id']]);
        $cards = $cards->fetchAll(PDO::FETCH_ASSOC);
        foreach($cards as $card):
    ?>
        <div class="card" onclick="if('<?= $card['link'] ?>' !== '#'){window.location='<?= $card['link'] ?>'}">
            <?php if($card['background_image']): ?>
                <img src="<?= htmlspecialchars($card['background_image']) ?>" alt="<?= htmlspecialchars($card['title']) ?>">
            <?php endif; ?>
            <h4><?= htmlspecialchars($card['title']) ?></h4>
        </div>
    <?php endforeach; ?>
    </div>
<?php endforeach; ?>
</main>

<footer>
  <div>Email: joelofficiel03@gmail.com</div>
  <div>Yaounde © 2026 EDUKAMERE</div>
</footer>

<!-- SIDEBAR -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
  <a href="home.php">Home</a>
  <a href="pastquest.php">Past Questions</a>
  <a href="quiz.php">Quiz</a>
  <a href="course.php">Course</a>
  <a href="books.php">Free Books</a>
  <a href="setting">Profile</a>
  <a href="logout.php">Logout</a>
</div>

<script>
function toggleSidebar(){
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  sidebar.classList.toggle("active");
  overlay.style.display = sidebar.classList.contains("active") ? "block" : "none";
}
</script>

</body>
</html>
