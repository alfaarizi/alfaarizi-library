<?php
session_start();

$logged_in = isset($_SESSION['username']);

require_once ("Storage.php");
function new_storage($filename)
{
  return new Storage(new JsonIO("$filename.json"), true);
}

if(!isset($_GET['id'])){ 
    header("Location: index.php"); 
    exit(); 
}

$errors = [];
$store_book = new_storage('book');
$book_id = $_GET['id'] ?? '';

$book = $store_book->findById($book_id);
if(!$book){ 
    $errors['id'] = 'No such book is available!';
    header("Location: index.php"); 
    exit(); 
}

// mark as read
$store_user = new_storage('user');
if($logged_in){
    $user = $store_user->findOne(['id' => $_SESSION['username']]);

    $user_read = []; // all users
    foreach($book['readBy'] as $rb) $user_read[] = $rb[0]; 

    if(in_array($user['id'], $user_read)){
        $user_index = 0;
        for($i = 0; $i < count($user_read); $i++) { if($user_read[$i] === $user['id']) { $user_index = $i; break; } }
        $book['readBy'][$user_index][1] = date('Y-m-d H:i:s');
    }else{
        $book['readBy'][] = [$user['id'], date('Y-m-d H:i:s')];
    }
    $store_book->update($book_id, $book);
}

header("Location: index.php"); 
exit(); 
