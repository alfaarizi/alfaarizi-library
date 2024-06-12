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

if(!$logged_in){ 
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

$review_errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $review_errors = [];

    $rating = $_POST['rating'] ?? '';
    $description = $_POST['description'] ?? '';

    if (trim($rating) === '') $review_errors['rating'] = 'The rating field is mandatory!';
    if (trim($description) === '') $review_errors['description'] = 'The description field is mandatory!';

    if(count($review_errors) == 0){
        $store_review = new_storage('review');
        $review =  $store_review->findOne(['bookID' => $book['id']]);
        
        if(!$review){
            $store_review->add([
                'bookID' => $book['id'],
                'reviews' => [[$_SESSION['username'], $rating, $description]],
            ]);
        }else{
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
                }
            }

            if($entry_key !== null){
                $target_review = $store_review->findById($entry_key);
                if($user_found){ // user is found
                    $target_review['reviews'][$user_index][1] = $rating;
                    $target_review['reviews'][$user_index][2] = $description;
                }else{ // user is not found
                    array_push($target_review['reviews'], [$_SESSION['username'], $rating, $description]);
                }
                $store_review->update($entry_key, $target_review);
            }


        }

        header("Location: profile.php?boook=" . $book['id']);
        exit;
    }
}



$store_user = new_storage('user');
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
        <h1><a href="index.php">IK-Library</a> > Review</h1>
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
            <div class="mb-3">
                <span class="h1 span-center">Review a book</span>
            </div>
            <?php if(count($errors) == 0): ?>
                <div class="book-details">
                    <div class="image">
                        <img src="assets/<?=$book['image']?>" alt="<?=$book['title']?>">
                    </div>
                    <div class="details">
                            <h2><?=$book['title'] . ' - ' . $book['author']?></h2>
                    </div>
                </div>
            <?php endif; ?>
            <br>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="rating" class="form-label"><strong>Your rating:</strong></label>
                    <div id="rating" class="rating">
                        <input type="radio" id="rate1" name="rating" value="1" <?php if(isset($rating) && $rating == '1') echo 'checked'; ?>>
                        <label for="rate1">1</label>
                        
                        <input type="radio" id="rate2" name="rating" value="2" <?php if(isset($rating) && $rating == '2') echo 'checked'; ?>>
                        <label for="rate2">2</label>
                        
                        <input type="radio" id="rate3" name="rating" value="3" <?php if(isset($rating) && $rating == '3') echo 'checked'; ?>>
                        <label for="rate3">3</label>
                        
                        <input type="radio" id="rate4" name="rating" value="4" <?php if(isset($rating) && $rating == '4') echo 'checked'; ?>>
                        <label for="rate4">4</label>
                        
                        <input type="radio" id="rate5" name="rating" value="5" <?php if(isset($rating) && $rating == '5') echo 'checked'; ?>>
                        <label for="rate5">5</label>
                    </div>
                    <span class="span-error"><?= $review_errors['rating'] ?? '' ?></span>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label"><strong>Your review:</strong></label><br>
                    <textarea id="description" name="description" rows="5" style="display: block; width: 100%;"><?= $description ?? '' ?></textarea><span class="span-error"><?= $review_errors['description'] ?? '' ?></span> 
                </div>
                <div class="mb-3">
                    <button type="reset" class="btn btn-danger" onclick="window.location.href='index.php'">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm</button>
                </div>
            </form>
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