<?php
session_start();
header('Content-Type: application/json');
include "connect.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode([
        'status' => 'error',
        'message' => 'Not logged in'
    ]);
    exit;
}

$uid = $_SESSION['user_id'];

try {

    // Get filters
    $level = $_GET['level'] ?? '';
    $subject = $_GET['subject'] ?? '';
    $year = $_GET['year'] ?? '';

    // Fetch user balance
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $balance = $stmt->fetchColumn();

    if($balance === false){
        $balance = 0;
    }

    // Base query
    $query = "SELECT * FROM past_questions WHERE 1";
    $params = [];

    if(!empty($level)){
        $query .= " AND level = ?";
        $params[] = $level;
    }

    if(!empty($subject)){
        $query .= " AND subject = ?";
        $params[] = $subject;
    }

    if(!empty($year)){
        $query .= " AND year = ?";
        $params[] = $year;
    }

    $query .= " ORDER BY year DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $questions = [];
    $answers = [];

    foreach($rows as $row){
        if($row['type'] === 'question'){
            $questions[] = $row;
        } else {
            $answers[] = $row;
        }
    }

    echo json_encode([
        'status' => 'success',
        'balance' => intval($balance),
        'questions' => $questions,
        'answers' => $answers
    ]);

} catch(PDOException $e){
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
