<?php
session_start();

$logged_in = isset($_SESSION['username']);
$is_admin = isset($_SESSION['username']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 'yes';

require_once ("Storage.php");
function new_storage($filename)
{
    return new Storage(new JsonIO("$filename.json"), true);
}

$store_user = new_storage('user');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $user_id = $_GET['user'] ?? '';
    $user = $store_user->findOne(['id' => $user_id]);
}

if(!isset($_GET['user'])){
    header("Location: index.php");
    exit;
}

if($logged_in){ 
    $user = $store_user->findOne(['id' => $_SESSION['username']]);

    $store_book = new_storage('book');
    $entries_book = $store_book->findAll();

    $books_read = [];
    foreach($entries_book as $book){
        foreach ($book['readBy'] as $rb) {
            if($rb[0] === $user['id']) $books_read[] = [$book['title'], $rb[1]];
        }
    }

    if(count($books_read) !== 0) {
        usort($books_read, function($a, $b) {
            $timestamp_a = strtotime($a[1]);
            $timestamp_b = strtotime($b[1]);
            return $timestamp_b <=> $timestamp_a; // descending order 
        });
    }
} 

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="bootstrap/css/style.css">

    <title>IK-Library | Home</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/cards.css">
    <link rel="stylesheet" href="styles/details.css">
    <link rel="stylesheet" href="styles/others.css">
</head>

<body>
    <header>
        <h1><a href="index.php">IK-Library</a> > Profile</h1>
    </header>

    <div class="wrapper d-flex align-items-stretch">
        <!-- Navigation begin -->
        <nav id="sidebar">
        <div class="custom-menu">
            <button type="button" id="sidebarCollapse" class="btn btn-primary"></button>
        </div>
        
        <div class="img bg-wrap text-center py-4" style="background-image: url(assets/archieves.jpg);">
            <div class="user-logo">
            <div class="img" style="background-image: url(<?= isset($user['profile']) ? 'assets/' . $user['profile'] : 'assets/guest.png'?>);"></div>
            <h3><a class="a-name" href="profile.php?user=<?= $user['id'] ?? 'guest'?>"><?= $user['username'] ?? 'Guest' ?></a></h3>
            </div>
        </div>
        
        <ul class="list-unstyled components mb-5">
            <li class="active">
            <a href="index.php"><span class="fa fa-home mr-3"></span> Home</a>
            </li>
            <?php if (!$logged_in): ?>
            <li>
            <a href="login.php"><span class="fa fa-sign-in mr-3"></span> Login</a>
            </li>
            <?php else: ?>
            <li>
            <a href="logout.php"><span class="fa fa-sign-out mr-3"></span> Logout</a>
            </li>
            <?php endif; ?>
            
        </ul>
        </nav>
        <!-- Navigation ends -->

        <!-- Page Content begin -->
        <div id="content" class="p-4 p-md-5 pt-5">
            <div class="mb-3">
                <span class="h1 span-center">User Profile</span>
            </div>
            <div class="img bg-wrap text-center py-4" style="background-image: url(assets/archieves.jpg);">
                <div class="user-logo">
                <div class="img" style="background-image: url(<?= isset($user['profile']) ? 'assets/' . $user['profile'] : 'assets/guest.png'?>);"></div>
                    <h3><?= $user['username'] ?? 'Guest' ?></h3>
                </div>
            </div>
            <br>
            <div class="book-details">
                <div class="details">
                    <p class="larger"><strong>Username : </strong><?= $user['username'] ?? 'Guest' ?></p>
                    <p class="larger"><strong>Email : </strong><?= $user['email'] ?? 'N/A' ?></p>
                    <p class="larger"><strong>Last login: </strong><?= $user['lastlogin'] ?? 'N/A' ?></p>
                    <p class="larger"><strong>Admin Authority: </strong><?= $user['admin'] ?? 'N/A' ?></p>
                    <p class="larger"><strong>ID: </strong><?= $user['id'] ?? 'N/A' ?></p>
                </div>
            </div>
            <br>
            <?php if(isset($books_read) && count($books_read) !== 0): ?>
                <h2>Books you have read</h2>
                <ul>
                <?php foreach($books_read as $read): ?>
                <li class="larger"><strong><?= $read[0] ?></strong>, at <?= $read[1] ?></li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <h2>You have not read any book. Start reading!</h2>
            <?php endif; ?>
        </div>
        <!-- Page Content end -->
    </div>

    <footer>
        <p>IK-Library | ELTE IK Webprogramming</p>
    </footer>

        <script src="bootstrap/js/jquery.min.js"></script>
        <script src="bootstrap/js/popper.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <script src="bootstrap/js/main.js"></script>
</body>

</html> 