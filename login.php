<?php
session_start();

require_once ("Storage.php");
function new_storage($filename)
{
    return new Storage(new JsonIO("$filename.json"), true);
}

$errors = [];
$store_user = new_storage('user');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    $username = $_POST['login_username'] ?? '';
    $password = $_POST['login_password'] ?? '';

    if (trim($username) === '') $errors['login_username'] = 'The username field is mandatory';
    if(trim($password) === '') $errors['login_password'] = 'The password field is mandatory!';
    
    $user = $store_user->findOne(['username' => $username]);
    if($user){
      if(!password_verify($password, $user['password'])) $errors['login_password'] = 'Password is incorrect!';
    }else{
      $errors['login_username'] = 'Username is not found!';
    }

    if(count($errors) == 0){
      $user['lastlogin'] = date('Y-m-d H:i:s') ?? '';
      $store_user->update($user['id'], $user);

      $_SESSION['username'] = $user['id'];
      $_SESSION['is_admin'] = $user['admin'] === 'yes' ? 'yes' : 'no';
      header("Location: profile.php?user=" . $user['id']);
      exit;
    }

}

?>

<!doctype html>
<html lang="en">

<head>
  <title>Sidebar 09</title>
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
        <li>
          <a href="login.php"><span class="fa fa-sign-in mr-3"></span> Login</a>
        </li>
      </ul>
    </nav>
    <!-- Navigation ends -->

    <!-- Page Content begin -->
    <div id="content" class="p-4 p-md-5 pt-5">
        <form action="" method="post">
            <div class="form-group">
                <label for="login_username" class="form-label">Username</label>    
                <input type="text" class="form-control" id="login_username" name="login_username"  placeholder="Username" value="<?= $username ?? '' ?>"><span class="span-error"><?= $errors['login_username'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label for="login_password" class="form-label">Password</label>    
                <input type="password" class="form-control" id="login_password" name="login_password" placeholder="Password" value="<?= $password ?? '' ?>"><span class="span-error"><?= $errors['login_password'] ?? '' ?></span>
            </div>
            <button type="submit" name="action" value="login" class="btn btn-info btn-block btn-round">Login</button>
        </form>
        <hr>
        <div>Don't have an account? <a href="signup.php" >Sign Up</a>.</div>
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