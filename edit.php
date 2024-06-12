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
    header("Location: index.php"); 
    exit(); 
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $errors = [];
    $image = $_FILES['image']['name'] ?? '';
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';  
    $year = $_POST['year'] ?? '';  
    $planet = $_POST['planet'] ?? ''; 
    $genre = $_POST['genre'] ?? '';
    $description = $_POST['description'] ?? '';

    if(trim($title) === '') $errors['title'] = 'The title field is mandatory!';
    if(trim($author) === '') $errors['author'] = 'The author field is mandatory!';

    if(trim($year) === '') $errors['year'] = 'The year field is mandatory!';
    else if(!filter_var($year, FILTER_VALIDATE_INT)) $errors['year'] = 'The year must be an integer!';
    else if(intval($year) < 1) $errors['year'] = 'The year must be a positive number!';

    if(count($genre) == 0) $errors['genre'] = 'The genre field is mandatory!';

    if(trim($planet) === '') $errors['planet'] = 'The planet field is mandatory!';
    if(trim($description) === '') $errors['description'] = 'The description field is mandatory!';

    if(trim($image) !== '') {
        $target_file = 'assets/' . basename($image);
        $accepted_images = ['jpg', 'png', 'jpeg'];
        
        $file_parts = explode('.', $image);
        $file_format = strtolower(end($file_parts));

        // if ( getimagesize($_FILES['image']['tmp_name']) == false) $errors['image'] = 'The file must be an image!'; 
        if ($_FILES['image']['size'] > 5000000) $errors['image'] = 'The image is too large! 5 MB limit';  // Check file size, 5MB limit
        if(!in_array($file_format, $accepted_images)) $errors['image'] = 'Invalid Image format! Only jpg, png, jpeg, heic are allowed'; // check file formats

        // Check if $errors array is still empty before uploading the file
        if (empty($errors)) {
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)){
                $errors['image'] = "There was an error uploading your file.";
            }
        }
    }else{
        $image = $book['image'];
    }

    if(count($errors) == 0){
        $book['image'] = $image;
        $book['title'] = $title;
        $book['author'] = $author;
        $book['year'] = $year;
        $book['planet'] = $planet;
        $book['genre'] = $genre;
        $book['description'] = $description;

        $store_book->update($book_id, $book);
        header("Location: details.php?id=$book_id");
        exit();
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
        <h1><a href="index.php">IK-Library</a> > Edit</h1>
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
                <span class="h1 span-center">edit a book</span>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="image" class="form-label">Book cover:</label><br>
                    <div class="image">
                        <img src="assets/<?=$book['image']?>" alt="<?=$book['title']?>">
                    </div>   
                    <input type="file" id="image" name="image" value="<?= $book['image'] ?? '' ?>"><br><span class="span-error"><?= $errors['image'] ?? '' ?></span> 
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Title:</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= $book['title'] ?? '' ?>"><span class="span-error"><?= $errors['title'] ?? '' ?></span>
                </div>
                <div class="mb-3">
                    <label for="author" class="form-label">Author:</label>
                    <input type="text" class="form-control" id="author" name="author" value="<?= $book['author'] ?? '' ?>"><span class="span-error"><?= $errors['author'] ?? '' ?></span>
                </div>
                <div class="mb-3">
                    <label for="year" class="form-label">Year:</label>
                    <input type="text" class="form-control" id="year" name="year" value="<?= $book['year'] ?? '' ?>"><span class="span-error"><?= $errors['year'] ?? '' ?></span>                
                </div>
                <div class="mb-3">
                    <label for="planet" class="form-label">Planet:</label>
                    <input type="text" class="form-control" id="planet" name="planet" value="<?= $book['planet'] ?? '' ?>"><span class="span-error"><?= $errors['planet'] ?? '' ?></span>
                </div>
                <div class="mb-3">
                    <label for="genre" class="form-label">Genre:</label><br>
                    <div class="custom-checkbox">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-romance" name="genre[]" value="Romance" <?= in_array('Romance', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-romance">Romance</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-adventure" name="genre[]" value="Adventure" <?= in_array('Adventure', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-adventure">Adventure</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-thriller" name="genre[]" value="Thriller" <?= in_array('Thriller', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-thriller">Thriller</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-fantasy" name="genre[]" value="Fantasy" <?= in_array('Fantasy', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-fantasy">Fantasy</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-ya" name="genre[]" value="Young Adult" <?= in_array('Young Adult', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-ya">Young Adult</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-mystery" name="genre[]" value="Mystery" <?= in_array('Mystery', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-mystery">Mystery</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-historical" name="genre[]" value="Historical" <?= in_array('Historical', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-historical">Historical</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-horror" name="genre[]" value="Horror" <?= in_array('Horror', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-horror">Horror</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-scifi" name="genre[]" value="Science Fiction" <?= in_array('Science Fiction', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-scifi">Science Fiction</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-humorous" name="genre[]" value="Humorous" <?= in_array('Humorous', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-humorous">Humorous</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="genre-western" name="genre[]" value="Western" <?= in_array('Western', $book['genre'] ?? []) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="genre-western">Western</label>
                        </div>
                    </div>
                    <span class="span-error"><?= $errors['genre'] ?? '' ?></span>
                </div>          
                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label><br>
                    <textarea id="description" name="description" rows="5" style="display: block; width: 100%;"><?= $book['description'] ?? '' ?></textarea><span class="span-error"><?= $errors['description'] ?? '' ?></span>
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