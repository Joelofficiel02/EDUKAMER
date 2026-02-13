<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Fetch user balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = :id");
$stmt->bindValue(':id', $uid, PDO::PARAM_INT);
$stmt->execute();
$userBalance = $stmt->fetchColumn();

// 🔥 Fetch available filters uploaded by admin
$levels = $conn->query("SELECT DISTINCT level FROM past_questions ORDER BY level ASC")->fetchAll(PDO::FETCH_COLUMN);
$subjects = $conn->query("SELECT DISTINCT subject FROM past_questions ORDER BY subject ASC")->fetchAll(PDO::FETCH_COLUMN);
$years = $conn->query("SELECT DISTINCT year FROM past_questions ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EDUKAMERE | Past Questions</title>
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
/* BALANCE DISPLAY */
.balance-container{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin:15px 20px;
  font-weight:bold;
  font-size:16px;
}
.balance-container button{
  padding:4px 8px;
  background:var(--orange);
  border:none;
  color:#fff;
  border-radius:5px;
  cursor:pointer;
  font-size:12px;
}
/* MAIN CONTENT */
main{ padding:20px; }
h2{ color:var(--green); margin-bottom:20px; }
/* FILTER BAR */
.filter-bar{
  display:flex;
  flex-wrap:wrap;
  gap:12px;
  margin-bottom:25px;
  justify-content:flex-start;
}

.filter-bar select{
  padding:10px 12px;
  border-radius:8px;
  border:1px solid #ccc;
  outline:none;
  font-size:14px;
  min-width:120px;
  flex: 1 1 150px; /* Responsive width */
  max-width:250px;  /* Prevent stretching on large screens */
  box-sizing:border-box;
}

/* For very small screens */
@media (max-width:480px){
  .filter-bar select{
    flex: 1 1 100%;
    font-size:13px;
  }
}

/* PAST QUESTION CARDS */
.pq-section{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
  gap:18px;
}
.pq-card{
  background:#fff;
  padding:12px;
  border-radius:10px;
  box-shadow:0 2px 6px rgba(0,0,0,0.1);
  transition:transform 0.2s;
  cursor:pointer;
  position:relative;
}
.pq-card:hover{ transform:scale(1.05); }
.pq-card h4{
  color:var(--green);
  margin:8px 0 4px;
  font-size:15px;
  text-align:center;
}
.pq-card p{
  font-size:13px;
  color:#555;
  text-align:center;
}
.pq-card a{
  display:block;
  margin-top:8px;
  font-size:13px;
  color:var(--orange);
  text-decoration:none;
  text-align:center;
}
/* SUBSCRIPTION LABEL */
.sub-label{
  position:absolute;
  top:8px;
  right:8px;
  background:var(--orange);
  color:#fff;
  padding:2px 6px;
  border-radius:4px;
  font-size:12px;
  font-weight:bold;
}
/* MODAL */
.modal{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.6);
  justify-content:center;
  align-items:center;
  z-index:6000;
}
.modal-content{
  background:#fff;
  padding:20px;
  border-radius:12px;
  width:90%;
  max-width:420px;
  position:relative;
}
.modal-content h3{
  color:var(--green);
  text-align:center;
  margin-bottom:12px;
}
.modal-content p{
  font-size:14px;
  margin-bottom:10px;
}
.modal-content input{
  width:100%;
  padding:8px;
  margin-bottom:10px;
  border-radius:6px;
  border:1px solid #ccc;
  outline:none;
}
.modal-content button{
  padding:10px 12px;
  background:var(--green);
  color:#fff;
  border:none;
  border-radius:6px;
  cursor:pointer;
}
.close{
  position:absolute;
  top:8px;
  right:12px;
  cursor:pointer;
  font-weight:bold;
}
/* FOOTER */
footer{
  background:var(--green);
  color:#fff;
  text-align:center;
  font-size:12px;
  padding:12px;
}
footer a{
  color:#fff; text-decoration:none; margin:0 5px;
}
footer a:hover{ color:var(--orange); }
.dark{
  --bg:#0f172a;
  --text:#f1f5f9;
}
.dark header,
.dark footer,
.dark .modal-content{
  background:#020617;
  color:#f1f5f9;
}
.dark input{
  background:#111827;
  color:#f1f5f9;
  border:none;
}

</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <h3>EDUKAMERE</h3>
  <a href="pastquest.php">📘 <span>Past Questions</span></a>
  <a href="quiz.php">📝 <span>Quiz</span></a>
  <a href="course.php">🎓 <span>Course</span></a>
  <a href="books.php">📚 <span>Free Books</span></a>
  <a href="setting.php">⚙️ <span>Profile</span></a>
  <a href="logout.php">🚪 <span>Logout</span></a>
</div>

<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<header>
  <div class="header-top">
    <button class="icon-btn" onclick="toggleMenu()">☰</button>
    <span class="logo">EDUKAMERE</span>
  </div>
</header>

<div class="balance-container">
  💰 Balance: <span id="userBalance"><?php echo $userBalance; ?></span> XAF
  <button onclick="openModal('topupModal')">Top-Up</button>
</div>

<main>
  <h2>📘 Past Questions</h2>

  <div class="filter-bar">

    <!-- LEVEL (UNCHANGED FROM YOUR ORIGINAL) -->
<select id="levelFilter">
  <option value="">Select Level</option>
  <?php foreach($levels as $lvl): ?>
    <option value="<?php echo htmlspecialchars($lvl); ?>">
      <?php echo htmlspecialchars($lvl); ?>
    </option>
  <?php endforeach; ?>
</select>

<select id="subjectFilter">
  <option value="">Select Subject</option>
  <?php foreach($subjects as $sub): ?>
    <option value="<?php echo htmlspecialchars($sub); ?>">
      <?php echo htmlspecialchars($sub); ?>
    </option>
  <?php endforeach; ?>
</select>

<select id="yearFilter">
  <option value="">Select Year</option>
  <?php foreach($years as $y): ?>
    <option value="<?php echo htmlspecialchars($y); ?>">
      <?php echo htmlspecialchars($y); ?>
    </option>
  <?php endforeach; ?>
</select>


  <div class="pq-section" id="pqSection"></div>

  <h2>📝 Answers</h2>
  <div class="pq-section" id="answersSection"></div>
</main>

<!-- FOOTER (UNCHANGED) -->
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

<!-- KEEP YOUR ORIGINAL MODALS AND JS EXACTLY AS THEY WERE -->
<script>

let userBalance = 0;
const userBalanceEl = document.getElementById("userBalance");

const levelEl = document.getElementById("levelFilter");
const subjectEl = document.getElementById("subjectFilter");
const yearEl = document.getElementById("yearFilter");

// Fetch Past Questions
function fetchPQ(){
    let params = new URLSearchParams({
        level: levelEl.value,
        subject: subjectEl.value,
        year: yearEl.value
    });

    fetch("ajax_get_pq.php?" + params.toString())
    .then(res => res.json())
    .then(data => {

        if(data.status !== 'success'){
            alert(data.message);
            return;
        }

        userBalance = data.balance;
        userBalanceEl.textContent = userBalance;

        const pqSection = document.getElementById("pqSection");
        const answersSection = document.getElementById("answersSection");

        pqSection.innerHTML = "";
        answersSection.innerHTML = "";

        // Build dynamic filters from returned data
        buildDynamicFilters(data);

        // Questions
        data.questions.forEach(row => {
            createCard(row, pqSection, false);
        });

        // Answers
        data.answers.forEach(row => {
            createCard(row, answersSection, true);
        });

    });
}

// Build filters dynamically
function buildDynamicFilters(data){

    // Subjects
    if(levelEl.value){
        let subjects = [...new Set(data.questions.map(q => q.subject))];
        subjectEl.innerHTML = '<option value="">Select Subject</option>';
        subjects.forEach(sub=>{
            subjectEl.innerHTML += `<option value="${sub}">${sub}</option>`;
        });
    }

    // Years
    if(levelEl.value && subjectEl.value){
        let years = [...new Set(
            data.questions
            .filter(q => q.subject === subjectEl.value)
            .map(q => q.year)
        )];

        yearEl.innerHTML = '<option value="">Select Year</option>';
        years.forEach(y=>{
            yearEl.innerHTML += `<option value="${y}">${y}</option>`;
        });
    }
}

// Create card
function createCard(row, container, isAnswer){

    const card = document.createElement("div");
    card.className = "pq-card";
    card.style.background="#fff";
    card.style.padding="12px";
    card.style.borderRadius="10px";
    card.style.boxShadow="0 2px 6px rgba(0,0,0,0.1)";
    card.style.cursor="pointer";

    let title = isAnswer
        ? `${row.level} ${row.subject} Answers`
        : `${row.level} ${row.subject} ${row.year}`;

    if(row.price > 0){
        card.innerHTML = `
            <h4>${title}</h4>
            <div style="color:red;font-weight:bold;">
                ${row.price} XAF to unlock
            </div>
        `;

        card.onclick = ()=>{
            if(userBalance >= row.price){
                if(confirm("Unlock for "+row.price+" XAF?")){
                    userBalance -= row.price;
                    userBalanceEl.textContent = userBalance;
                    card.innerHTML = `
                        <h4>${title}</h4>
                        <a href="${row.file_path}" target="_blank">
                            Download PDF
                        </a>
                    `;
                }
            } else {
                alert("Insufficient balance");
            }
        };
    } else {
        card.innerHTML = `
            <h4>${title}</h4>
            <a href="${row.file_path}" target="_blank">
                Download PDF
            </a>
        `;
    }

    container.appendChild(card);
}

// Filter events
levelEl.addEventListener("change", ()=>{
    subjectEl.innerHTML = '<option value="">Select Subject</option>';
    yearEl.innerHTML = '<option value="">Select Year</option>';
    fetchPQ();
});

subjectEl.addEventListener("change", ()=>{
    yearEl.innerHTML = '<option value="">Select Year</option>';
    fetchPQ();
});

yearEl.addEventListener("change", fetchPQ);

// Initial load
fetchPQ();

fetchPQ();

</script>

</body>
</html>
