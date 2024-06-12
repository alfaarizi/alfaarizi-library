<?php
session_start();

$logged_in = isset($_SESSION['username']);
$is_admin = isset($_SESSION['username']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 'yes';

require_once ("Storage.php");

function new_storage($filename)
{
  return new Storage(new JsonIO("$filename.json"), true);
}

$errors = [];
$store_book = new_storage('book');
$entries_book = $store_book->findAll();

$filtered_genre = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $filtered_genre = $_POST['genre'] ?? [];
}
function getAverageRating($book){
  $book_id = $book['id'];
  
  $store_review = new_storage('review');
  $entries_review = $store_review->findAll();
  
  $total_rating = 0; $count_rating = 0;
  foreach($entries_review as $val){
    if($val['bookID'] === $book_id){
      foreach($val['reviews'] as $rev){
        $total_rating += $rev[1];
        $count_rating++;
      }
      break;
    }
  }
  return $count_rating !== 0 ? round($total_rating/$count_rating, 2) : 0;
}

if (!empty($filtered_genre)) {
  $entries_book = array_filter($entries_book, function($book) use ($filtered_genre) {
    foreach ($filtered_genre as $gen) {
      if (!in_array($gen, $book['genre'])) return false;
    }
    return true;
  });
}

$books_read = [];
if($logged_in){
  $store_user = new_storage('user');
  $user = $store_user->findOne(['id' => $_SESSION['username']]);

  usort($entries_book, fn($a, $b) => getAverageRating($b) <=> getAverageRating($a));

  foreach($entries_book as $book){
      foreach ($book['readBy'] as $rb) {
          if($rb[0] === $user['id']) $books_read[] = $book['title'];
      }
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
    <h1><a href="index.php">IK-Library</a> > Home</h1>
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
      <form action="" method="post" id="genre-form">
        <div class="mb-3">
          <p><strong>Filter by Genre:</strong></p>
        </div>
        <div class="mb-3">
            <input type="checkbox" class="genre-checkbox" id="genre-romance" name="genre[]" value="Romance" <?= in_array('Romance', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-romance">Romance</label>

            <input type="checkbox" class="genre-checkbox" id="genre-adventure" name="genre[]" value="Adventure" <?= in_array('Adventure', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-adventure">Adventure</label>

            <input type="checkbox" class="genre-checkbox" id="genre-thriller" name="genre[]" value="Thriller"<?= in_array('Thriller', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <labe for="genre-thriller">Thriller</label>

            <input type="checkbox" class="genre-checkbox" id="genre-fantasy" name="genre[]" value="Fantasy"<?= in_array('Fantasy', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-fantasy">Fantasy</label>

            <input type="checkbox" class="genre-checkbox" id="genre-ya" name="genre[]" value="Young Adult" <?= in_array('Young Adult', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-ya">Young Adult</label>

            <input type="checkbox" class="genre-checkbox" id="genre-mystery" name="genre[]" value="Mystery" <?= in_array('Mystery', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-mystery">Mystery</label>

            <input type="checkbox" class="genre-checkbox" id="genre-historical" name="genre[]" value="Historical"<?= in_array('Historical', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-historical">Historical</label>

            <input type="checkbox" class="genre-checkbox" id="genre-horror" name="genre[]" value="Horror" <?= in_array('Horror', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-horror">Horror</label>

            <input type="checkbox" class="genre-checkbox" id="genre-scifi" name="genre[]" value="Science Fiction"<?= in_array('Science Fiction', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-scifi">Science Fiction</label>

            <input type="checkbox" class="genre-checkbox" id="genre-humorous" name="genre[]" value="Humorous" <?= in_array('Humorous', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-humorous">Humorous</label>

            <input type="checkbox" class="genre-checkbox" id="genre-western" name="genre[]" value="Western" <?= in_array('Western', $filtered_genre ?? []) ? 'checked' : '' ?>>
            <label for="genre-western">Western</label>
        </div>
      </form>
      
      <?php if($is_admin): ?>
        <div class="mb-3 centered-div">
          <a href="add.php" class="pretty-link">Add a new book</a>
        </div>
      <?php endif; ?>
      <hr><br>
      <?php if(count($entries_book) > 0): ?>
        <div id="card-list">
          <?php foreach ($entries_book as $book): ?>
            <div class="book-card">
              <div class="image">
                <img src="assets/<?= $book['image'] ?>" alt="">
              </div>
              <div class="details">
                <h2><a href="details.php?id=<?= $book['id'] ?>"><?= $book['author'] . ' - ' . $book['title']?> <?php if(in_array($book['title'], $books_read)): ?><span class="larger" style="color: green;">&#10003</span><?php else: ?><span class="larger" style="color: red;">&#10007;</span><?php endif; ?></a></h2>
              </div>
              <?php if($logged_in): ?>
                <div class="edit">
                <span><a href="read.php?id=<?= $book['id'] ?>">Read</a></span> 
                <span><a href="review.php?id=<?= $book['id'] ?>"> | Review</a></span> 
                  <?php if($is_admin): ?>    
                    <span><a href="edit.php?id=<?= $book['id'] ?>"> | Edit</a></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <h2 style="text-align: center;">No books have been found</h2>
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
  <script>
    document.querySelectorAll('.genre-checkbox').forEach(function(checkbox) {
      checkbox.addEventListener('change', function() {
        document.getElementById('genre-form').submit();
      });
    });
  </script>
</body>

</html>