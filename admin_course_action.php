<?php
include "connect.php";
$action = $_GET['action'] ?? '';

if($action=='get' && isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}

if($action=='delete' && isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    echo json_encode(['status'=>'success']);
}

if($action=='save'){
    $id = $_POST['id'] ?? '';
    $level = $_POST['level'];
    $title = $_POST['title'];
    $approach = $_POST['approach'];
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;

    if($id){
        $stmt = $conn->prepare("UPDATE courses SET level=:level, title=:title, approach=:approach, description=:description, price=:price WHERE id=:id");
        $stmt->execute([':level'=>$level, ':title'=>$title, ':approach'=>$approach, ':description'=>$description, ':price'=>$price, ':id'=>$id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (level,title,approach,description,price) VALUES (:level,:title,:approach,:description,:price)");
        $stmt->execute([':level'=>$level, ':title'=>$title, ':approach'=>$approach, ':description'=>$description, ':price'=>$price]);
    }
    echo json_encode(['status'=>'success']);
}
