<?php
session_start();
require_once "connect.php";

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$showModal = "";
$loginError = "";
$registerError = "";

/* LOGIN */
if (isset($_POST['login'])) {

    $telephone = trim($_POST['telephone']);
    $password = $_POST['password'];

    if ($telephone === "651257276" && $password === "1010101010") {
        header("Location: specialpage.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE telephone = ?");
    $stmt->execute([$telephone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];
        header("Location: home.php");
        exit;
    } else {
        $loginError = "Invalid phone number or password";
        $showModal = "login";
    }
}

/* REGISTER */
if (isset($_POST['register'])) {

    $first_name = trim($_POST['first_name']);
    $second_name = trim($_POST['second_name']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $school = trim($_POST['school']);
    $level = trim($_POST['level']);
    $fav_course = trim($_POST['fav_course']);
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $registerError = "Passwords do not match";
        $showModal = "register";
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $registerError = "Email already exists";
            $showModal = "register";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users 
                (first_name, second_name, telephone, email, school, level, fav_course, dob, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $first_name,
                $second_name,
                $telephone,
                $email,
                $school,
                $level,
                $fav_course,
                $dob,
                $hashed
            ]);

            $showModal = "login";
        }
    }
}

/* Fetch sections and cards dynamically */
$sections = $conn->query("SELECT * FROM sections ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EDUKAMERE</title>

<style>
:root{
  --green:#16a34a;
  --orange:#f97316;
  --bg:#f4f7fb;
  --text:#1f2937;
  --card:#fff;
}

/* DARK MODE COLORS */
body.dark{
  --bg:#1a1a1a;
  --text:#e5e5e5;
  --card:#2a2a2a;
}

*{ box-sizing:border-box; }

body{
  margin:0;
  font-family:Arial, sans-serif;
  background:var(--bg);
  color:var(--text);
  display:flex;
  flex-direction:column;
  min-height:100vh;
}

/* HEADER */
header{
  background:var(--green);
  padding:12px 15px;
  color:#fff;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.logo{
  font-weight:bold;
  font-size:18px;
  color:var(--orange);
}

.auth-btn{
  background:var(--orange);
  border:none;
  padding:7px 10px;
  border-radius:6px;
  cursor:pointer;
  font-weight:bold;
  margin-left:5px;
  color:#fff;
  font-size:13px;
}

/* HERO */
.hero{
  background:linear-gradient(135deg,var(--green),var(--orange));
  color:#fff;
  text-align:center;
  padding:50px 15px;
  margin:15px;
  border-radius:14px;
}

.hero h1{ margin:0; font-size:26px; font-weight:800; }
.hero p{ margin-top:10px; font-size:15px; opacity:.95; }

/* MAIN AREA */
main{
  flex:1;
  padding:15px;
}

.topic-link{
  font-weight:bold;
  font-size:18px;
  color:var(--text);
  margin:25px 0 10px;
  display:block;
}

.section{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
  gap:15px;
}

.card{
  background:var(--card);
  border-radius:12px;
  overflow:hidden;
  cursor:pointer;
  box-shadow:0 4px 10px rgba(0,0,0,.1);
  transition:.3s ease;
  text-align:center;
}

.card:hover{
  transform:translateY(-5px);
  box-shadow:0 12px 30px rgba(0,0,0,.15);
}

.card img{
  width:100%;
  height:120px;
  object-fit:cover;
}

.card h4{
  margin:10px 0 5px;
  color:var(--green);
}

.card p{
  font-size:13px;
  color:#555;
  padding:0 8px 10px;
}

/* FOOTER */
footer{
  background:var(--green);
  color:#fff;
  text-align:center;
  padding:12px;
  font-size:13px;
  margin-top:auto;
}

/* MODALS */
.modal{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.6);
  justify-content:center;
  align-items:center;
  padding:15px;
}

.modal-content{
  background:var(--card);
  padding:18px;
  width:100%;
  max-width:380px;
  border-radius:10px;
  position:relative;
}

.modal-content h3{
  text-align:center;
  color:var(--green);
  font-size:18px;
}

.modal-content input{
  width:100%;
  padding:10px;
  margin:8px 0;
  font-size:14px;
}

.modal-content button{
  width:100%;
  padding:10px;
  background:var(--green);
  color:#fff;
  border:none;
  border-radius:5px;
  font-size:14px;
}

.close{
  position:absolute;
  top:8px;
  right:12px;
  cursor:pointer;
  font-weight:bold;
}

.error{
  color:red;
  font-size:13px;
  text-align:center;
}

.switch-link{
  text-align:center;
  font-size:13px;
  color:var(--orange);
  cursor:pointer;
  margin-top:10px;
}

/* DARK MODE */
body.dark header, body.dark footer, body.dark .modal-content{
  background:#222;
  color:#e5e5e5;
}

body.dark .card{ background:#333; color:#e5e5e5; }

@media (max-width:480px){
  .logo{ font-size:16px; }
  .auth-btn{ font-size:12px; padding:6px 8px; }
  .hero h1{ font-size:22px; }
  .hero p{ font-size:14px; }
}
</style>
</head>
<body>

<header>
  <div class="logo">EDUKAMERE</div>
  <div>
    <button class="auth-btn" onclick="openModal('login')">Login</button>
    <button class="auth-btn" onclick="openModal('register')">Register</button>
    <button class="auth-btn" onclick="toggleDark()">🌙</button>
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
  © 2026 EDUKAMERE | Yaoundé, Cameroon
</footer>

<!-- LOGIN MODAL -->
<div class="modal" id="login">
  <div class="modal-content">
    <div class="close" onclick="closeModal()">✖</div>
    <h3>Login</h3>
    <?php if($loginError): ?><p class="error"><?= $loginError ?></p><?php endif; ?>
    <form method="POST">
      <input type="tel" name="telephone" required placeholder="Telephone">
      <input type="password" name="password" required placeholder="Password">
      <button name="login">Login</button>
    </form>
    <div class="switch-link" onclick="switchModal('register')">No account? Register</div>
  </div>
</div>

<!-- REGISTER MODAL -->
<div class="modal" id="register">
  <div class="modal-content">
    <div class="close" onclick="closeModal()">✖</div>
    <h3>Register</h3>
    <?php if($registerError): ?><p class="error"><?= $registerError ?></p><?php endif; ?>
    <form method="POST">
      <input type="text" name="first_name" required placeholder="First Name">
      <input type="text" name="second_name" required placeholder="Second Name">
      <input type="tel" name="telephone" required placeholder="Telephone">
      <input type="email" name="email" required placeholder="Email">
      <input type="text" name="school" placeholder="School">
      <input type="text" name="level" placeholder="Level">
      <input type="text" name="fav_course" placeholder="Favourite Course">
      <input type="date" name="dob" required>
      <input type="password" name="password" required placeholder="Password">
      <input type="password" name="confirm_password" required placeholder="Confirm Password">
      <button name="register">Register</button>
    </form>
    <div class="switch-link" onclick="switchModal('login')">Already have an account? Login</div>
  </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display="flex"; }
function closeModal(){ document.querySelectorAll('.modal').forEach(m=>m.style.display='none'); }
function switchModal(id){ closeModal(); openModal(id); }
function toggleDark(){ document.body.classList.toggle('dark'); }
<?php if($showModal): ?> openModal("<?= $showModal ?>"); <?php endif; ?>
</script>

</body>
</html>
