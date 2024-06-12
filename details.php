<?php 
session_start();

$logged_in = isset($_SESSION['username']);
$is_admin = isset($_SESSION['username']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 'yes';

require_once("Storage.php");
function new_storage($filename){
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

// Show review if logged in
if($logged_in){
    $store_review = new_storage('review');
    $entries_review = $store_review->findAll();

    $user_found = false;
    $entry_key = null;
    $user_index = 0;
    foreach($entries_review as $key => $val){
        if($val['bookID'] === $book['id']){
            $entry_key = $key;
            foreach($val['reviews'] as $rev){
                if($rev[0] === $_SESSION['username']) {
                    $user_found = true;
                    break;
                };
                $user_index++;
            }
            break;
        }
    }

    $store_user = new_storage('user');
    $user_review = []; $other_review = [];
    
    $total_rating = 0;
    $count_rating = 0;
    if($entry_key !== null){
        $target_review = $store_review->findById($entry_key);
        if($user_found){
            $rat = $target_review['reviews'][$user_index][1];
            $total_rating += $rat; $count_rating++;
            $user_review = [$rat, $target_review['reviews'][$user_index][2]];
        }

        for($i = 0; $i < count($target_review['reviews']); $i++) {
            if($i === $user_index) continue;
            $username_aux = $store_user->findOne(['id' => $target_review['reviews'][$i][0]])['username'];

            $rat = $target_review['reviews'][$i][1];
            $total_rating += $rat; $count_rating++;
            $other_review[] = [$username_aux, $rat, $target_review['reviews'][$i][2]];
        }    
    }

    $average_rating = $count_rating !== 0 ? round($total_rating/$count_rating, 2) : 0;

    usort($book['readBy'], function($a, $b) {
        $timestamp_a = strtotime($a[1]);
        $timestamp_b = strtotime($b[1]);
        return $timestamp_b <=> $timestamp_a; // descending order 
    });
}



if($logged_in) $user = $store_user->findOne(['id' => $_SESSION['username']]);

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
        <h1><a href="index.php">IK-Library</a> > Book Details</h1>
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

        <!-- Page Content  -->
        <div id="content" class="p-4 p-md-5 pt-5">
            <?php if(count($errors) == 0): ?>
                <div class="book-details">
                    <div class="image">
                        <img src="assets/<?=$book['image']?>" alt="<?=$book['title']?>">
                    </div>
                    <div class="details">
                        <h2><?=$book['title']?></h2>
                        <p><strong>Author:</strong> <?=$book['author']?></p>
                        <p><strong>Year:</strong> <?=$book['year']?></p>
                        <p><strong>Planet:</strong> <?=$book['planet']?></p>
                        <p><strong>Genre:</strong> <?= implode(', ', $book['genre']) ?></p>
                        <p><strong>Average Rating:</strong> <?= isset($average_rating) ? $average_rating . ' / 5' : 'N/A' ?></p>
                        <p><strong>Description:</strong> <?=$book['description']?></p>
                        <br>
                        <?php if(!$logged_in): ?>
                            <h2>Please Login to see the reviews!</h2>
                        <?php else: ?>
                            <?php if(count($book['readBy']) !== 0): ?>
                                <h2>Users who read the book</h2>
                                <ul>
                                    <?php foreach($book['readBy'] as $rb): ?>
                                        <?php 
                                            $read = $store_user->findOne(['id' => $rb[0]]);
                                            $read_name = $read['username'];
                                        ?>
                                        <li><strong><span style="color:<?= $read_name === $user['username'] ? 'green' : '' ?>"><?=$read_name?><?= $read_name === $user['username'] ? ' (You)' : '' ?><?= ', at ' . $rb[1]?></span></strong></li>  
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <h2>No one has read the book</h2>
                            <?php endif; ?>
                            <br>
                            <?php if($entry_key !== null): ?>
                                <?php if(count($user_review) != 0): ?>
                                    <h2>Your Review</h2>
                                    <ul class="pretty-list">
                                        <li><strong>Rating:</strong> <?=$user_review[0]?></li>
                                        <li><strong>Review:</strong> <?=$user_review[1]?></li>
                                    </ul>
                                <?php else: ?><h2>Your Review</h2><p>None</p><?php endif; ?>
                                <br>
                                <?php if(count($other_review) != 0): ?>
                                    <h2>Others Review</h2>
                                    <?php foreach($other_review as $rev): ?>
                                    <ul class="pretty-list">
                                        <li><strong><?= $rev[0] ?>'s Review</strong></li>
                                        <ul>
                                            <li><strong>Rating:</strong> <?=$rev[1]?></li>
                                            <li><strong>Review:</strong> <?=$rev[2]?></li>
                                        </ul>
                                    </ul>
                                    <?php endforeach; ?>
                                <?php else: ?><h2>Others Review</h2><p>None</p><?php endif; ?>
                            <?php else: ?>
                                <h2>No Reviews</h2>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
       
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