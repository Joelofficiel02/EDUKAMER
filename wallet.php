<?php
session_start();
include "connect.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM wallet_transactions 
                        WHERE user_id = :uid 
                        ORDER BY created_at DESC");
$stmt->execute([':uid'=>$uid]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Wallet Transaction History</h2>

<table border="1" width="100%" cellpadding="10">
<tr>
    <th>Type</th>
    <th>Amount (XAF)</th>
    <th>Description</th>
    <th>Date</th>
</tr>

<?php foreach($transactions as $t): ?>
<tr>
    <td><?= strtoupper($t['type']) ?></td>
    <td><?= $t['amount'] ?></td>
    <td><?= htmlspecialchars($t['description']) ?></td>
    <td><?= $t['created_at'] ?></td>
</tr>
<?php endforeach; ?>
</table>
