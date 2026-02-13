<?php
session_start();
require_once "connect.php";

// -------------------- Handle Sections --------------------

// Add Section
if(isset($_POST['add_section'])){
    $title = $_POST['title'];
    $icon  = $_POST['icon'];
    $link  = $_POST['link'] ?? null;
    $conn->prepare("INSERT INTO sections (title, icon, link) VALUES (?, ?, ?)")
         ->execute([$title, $icon, $link]);
    header("Location: admin_panel.php"); exit;
}

// Edit Section
if(isset($_POST['edit_section'])){
    $id    = $_POST['id'];
    $title = $_POST['title'];
    $icon  = $_POST['icon'];
    $link  = $_POST['link'] ?? null;
    $conn->prepare("UPDATE sections SET title=?, icon=?, link=? WHERE id=?")
         ->execute([$title, $icon, $link, $id]);
    header("Location: admin_panel.php"); exit;
}

// Delete Section
if(isset($_GET['delete_section'])){
    $id = $_GET['delete_section'];
    $conn->prepare("DELETE FROM sections WHERE id=?")->execute([$id]);
    header("Location: admin_panel.php"); exit;
}

// -------------------- Handle Cards --------------------

// Add Card
if(isset($_POST['add_card'])){
    $section_id = $_POST['section_id'];
    $title      = $_POST['title'];
    $desc       = $_POST['description'];
    $link       = $_POST['link'] ?? '#';

    // Handle background image upload
    $bg_path = null;
    if(isset($_FILES['background_image']) && $_FILES['background_image']['error'] == 0){
        $uploadDir = 'uploads/cards/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = time().'_'.basename($_FILES['background_image']['name']);
        $targetFile = $uploadDir.$filename;
        if(move_uploaded_file($_FILES['background_image']['tmp_name'], $targetFile)){
            $bg_path = $targetFile;
        }
    }

    $conn->prepare("INSERT INTO cards (section_id, title, description, link, background_image) VALUES (?, ?, ?, ?, ?)")
         ->execute([$section_id, $title, $desc, $link, $bg_path]);
    header("Location: admin_panel.php"); exit;
}

// Edit Card
if(isset($_POST['edit_card'])){
    $id    = $_POST['id'];
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    $link  = $_POST['link'] ?? '#';

    // Handle background image upload
    $bg_path = $_POST['existing_image'] ?? null;
    if(isset($_FILES['background_image']) && $_FILES['background_image']['error'] == 0){
        $uploadDir = 'uploads/cards/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = time().'_'.basename($_FILES['background_image']['name']);
        $targetFile = $uploadDir.$filename;
        if(move_uploaded_file($_FILES['background_image']['tmp_name'], $targetFile)){
            $bg_path = $targetFile;
        }
    }

    $conn->prepare("UPDATE cards SET title=?, description=?, link=?, background_image=? WHERE id=?")
         ->execute([$title, $desc, $link, $bg_path, $id]);
    header("Location: admin_panel.php"); exit;
}

// Delete Card
if(isset($_GET['delete_card'])){
    $id = $_GET['delete_card'];
    $conn->prepare("DELETE FROM cards WHERE id=?")->execute([$id]);
    header("Location: admin_panel.php"); exit;
}

// -------------------- Fetch Sections --------------------
$sections = $conn->query("SELECT * FROM sections ORDER BY sort_order ASC")->fetchAll();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - EDUKAMERE</title>
    <style>
        body{font-family:Arial,sans-serif;padding:20px;background:#f4f4f4;}
        h1, h2{color:#16a34a;}
        .section, .card{background:#fff;padding:10px;margin:10px 0;border-radius:5px;box-shadow:0 1px 5px rgba(0,0,0,0.1);}
        .card{margin-left:20px;}
        form{margin:5px 0;}
        input, textarea{padding:5px;margin:3px 0;width:90%;}
        button{padding:5px 10px;margin:3px;}
        a.delete-link{color:red;text-decoration:none;margin-left:10px;}
        img.bg-preview{max-width:150px;margin-top:5px;border-radius:5px;}
    </style>
</head>
<body>
<h1>Admin Panel - EDUKAMERE</h1>

<!-- Add Section -->
<div class="section">
    <h2>Add Section</h2>
    <form method="POST">
        <input type="text" name="title" placeholder="Section Title" required>
        <input type="text" name="icon" placeholder="Icon Emoji">
        <input type="text" name="link" placeholder="Link URL (optional)">
        <button name="add_section">Add Section</button>
    </form>
</div>

<!-- List Sections -->
<?php foreach($sections as $sec): ?>
<div class="section">
    <h2><?= htmlspecialchars($sec['icon'].' '.$sec['title']) ?>
        <a class="delete-link" href="?delete_section=<?= $sec['id'] ?>" onclick="return confirm('Delete this section?')">[Delete]</a>
    </h2>

    <!-- Edit Section -->
    <form method="POST">
        <input type="hidden" name="id" value="<?= $sec['id'] ?>">
        <input type="text" name="title" value="<?= htmlspecialchars($sec['title']) ?>" required>
        <input type="text" name="icon" value="<?= htmlspecialchars($sec['icon']) ?>">
        <input type="text" name="link" value="<?= htmlspecialchars($sec['link']) ?>" placeholder="Link URL (optional)">
        <button name="edit_section">Edit Section</button>
    </form>

    <!-- Add Card -->
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="section_id" value="<?= $sec['id'] ?>">
        <input type="text" name="title" placeholder="Card Title" required>
        <textarea name="description" placeholder="Description"></textarea>
        <input type="text" name="link" placeholder="Link URL (optional)">
        <input type="file" name="background_image">
        <button name="add_card">Add Card</button>
    </form>

    <!-- List Cards -->
    <?php
    $cards = $conn->prepare("SELECT * FROM cards WHERE section_id=? ORDER BY sort_order ASC");
    $cards->execute([$sec['id']]);
    $cards = $cards->fetchAll();
    ?>
    <?php foreach($cards as $card): ?>
        <div class="card">
            <h3><?= htmlspecialchars($card['title']) ?>
                <a class="delete-link" href="?delete_card=<?= $card['id'] ?>" onclick="return confirm('Delete this card?')">[Delete]</a>
            </h3>
            <p><?= htmlspecialchars($card['description']) ?></p>
            <?php if($card['background_image']): ?>
                <img src="<?= htmlspecialchars($card['background_image']) ?>" class="bg-preview" alt="Background">
            <?php endif; ?>

            <!-- Edit Card -->
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $card['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($card['background_image']) ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($card['title']) ?>" required>
                <textarea name="description"><?= htmlspecialchars($card['description']) ?></textarea>
                <input type="text" name="link" value="<?= htmlspecialchars($card['link']) ?>" placeholder="Link URL (optional)">
                <input type="file" name="background_image">
                <button name="edit_card">Edit Card</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>
</body>
</html>
