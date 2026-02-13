<?php
header('Content-Type: application/json');
session_start();
include "connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

$uid = $_SESSION['user_id'];

if (!isset($_POST['amount']) || !is_numeric($_POST['amount'])) {
    echo json_encode(['success'=>false,'error'=>'Invalid amount']);
    exit;
}

$amount = intval($_POST['amount']);

if ($amount <= 0) {
    echo json_encode(['success'=>false,'error'=>'Amount must be greater than 0']);
    exit;
}

try {

    // Update balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance + :amt WHERE id = :id");
    $stmt->execute([':amt'=>$amount, ':id'=>$uid]);

    // Record transaction
    $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) 
                            VALUES (:uid, :amt, 'credit', 'Wallet Top-Up')");
    $stmt->execute([':uid'=>$uid, ':amt'=>$amount]);

    // Get updated balance
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = :id");
    $stmt->execute([':id'=>$uid]);
    $balance = $stmt->fetchColumn();

    echo json_encode(['success'=>true,'balance'=>$balance]);

} catch(PDOException $e){
    echo json_encode(['success'=>false,'error'=>'Database error']);
}
