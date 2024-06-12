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

    $name = $_POST['signup_name'] ?? '';
    $email = $_POST['signup_email'] ?? '';
    $password = $_POST['signup_password'] ?? '';
    $confirm_password = $_POST['signup_confirm_password'] ?? '';
    $is_admin = $_POST['is_admin'] ?? '';

    $check_username = $store_user->findOne(['username' => $name]);
    if(trim($name) === '') $errors['signup_name'] = 'The name field is mandatory!';
    else if($check_username) $errors['signup_name'] = 'Username is used, please log in';

    $check_email = $store_user->findOne(['email' => $email]);
    if (trim($email) === '') $errors['signup_email'] = 'The email field is mandatory';
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['signup_email'] = 'The email must be in the correct format!';
    else if($check_email) $errors['signup_email'] = 'Email is used, please log in';

    if(trim($password) === '') $errors['signup_password'] = 'The password field is mandatory!';

    if(trim($confirm_password) === '') $errors['signup_confirm_password'] = 'The confirm password field is mandatory!';
    else if($confirm_password !== $password)  $errors['signup_confirm_password'] = 'The confirm password is incorrect!';

    $accepted_admin_id = ['admin01', 'admin02', 'admin03'];
    if($is_admin === 'on'){
      $admin_id = $_POST['admin_id'] ?? '';
      if (trim($admin_id) === '') $errors['admin_id'] = 'The admin field is mandatory!';
      else if(!in_array($admin_id, $accepted_admin_id)) $errors['admin_id'] = 'Invalid admin id!';
    }

    if(count($errors) == 0){    
        $store_user->add([
            'username' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'lastlogin' => date('Y-m-d H:i:s'),
            'admin' => $is_admin ? 'yes' : 'no',
            'profile' => 'profile0' . rand(1, 5) . '.png'
        ]);

        $user = $store_user->findOne(['email' => $email]);
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
                <label for="signup_name" class="form-label">Your name</label>    
                <input type="text" class="form-control" id="signup_name" name="signup_name" placeholder="Name" value="<?= $name ?? '' ?>"><span class="span-error"><?= $errors['signup_name'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label for="signup_email" class="form-label">Email address</label>    
                <input type="email" class="form-control" id="signup_email" name="signup_email" placeholder="name@email.com" value="<?= $email ?? '' ?>"><span class="span-error"><?= $errors['signup_email'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label for="signup_password" class="form-label">Password</label>    
                <input type="password" class="form-control" id="signup_password" name="signup_password" placeholder="Password" value="<?= $password ?? '' ?>"><span class="span-error"><?= $errors['signup_password'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label for="signup_confirm_password" class="form-label">Confirm Password</label>    
                <input type="password" class="form-control" id="signup_confirm_password" name="signup_confirm_password" placeholder="Confirm Password" value="<?= $confirm_password ?? '' ?>"><span class="span-error"><?= $errors['signup_confirm_password'] ?? '' ?></span>
            </div>
            <!-- Admin Checkbox -->
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" <?= isset($_POST['is_admin']) && $_POST['is_admin'] === 'on' ? 'checked' : '' ?>>    
                <label class="form-check-label" for="is_admin">Are you an admin?</label>    
            </div>
            <div class="form-group" id="admin_id_group" style="display: none;">
                <label for="admin_id" class="form-label">Admin ID</label>
                <input type="text" class="form-control" id="admin_id" name="admin_id" placeholder="Admin ID" value="<?= $admin_id ?? '' ?>"><span class="span-error"><?= $errors['admin_id'] ?? '' ?></span>
            </div>
            <button type="submit" name="action" value="signup" class="btn btn-info btn-block btn-round">Sign Up</button>
        </form>
        <hr>
        <div>Already have an account? <a href="login.php" >Login</a>.</div>
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
    // toggle admin ID input field
    $('#is_admin').on('change', function () {
        if ($(this).is(':checked')) $('#admin_id_group').show();
        else $('#admin_id_group').hide();
    }).trigger('change'); 
  </script>
</body>

</html>