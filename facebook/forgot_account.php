<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: welcome.php');
    exit;
}
include "_db_connect.php";


$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginId = $_POST['loginId'];
    $loginPassword = $_POST['loginPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate the login credentials
    if (empty($loginId)) {
        $errors['loginId'] = "Please enter your email address or phone number.";
    }

    if (empty($loginPassword)) {
        $errors['loginPassword'] = "Please enter your password.";
    }
    if (empty($confirmPassword)) {
        $errors['confirmPassword'] = "Please enter your password.";
    }
    elseif( $confirmPassword != $loginPassword )
    {
        $errors['confirmPassword'] = "Both passwords must be same!.";
    }

    if (count($errors) === 0) {
        $sql = "SELECT * FROM user_data WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $loginId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) 
        {

            $row = mysqli_fetch_assoc($result);

            $hash = password_hash($loginPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE user_data SET user_password=? WHERE user_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $hash, $loginId);
            $result = mysqli_stmt_execute($stmt);

            if( $result )
            {
                header('Location: index.php?pwd_success=1');
                exit();
            }
        } 
        else 
        {
            $errors['loginId'] = "User does not exist with this phone or email.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Hub</title>
    <link rel="icon" type="image/png" href="img/social_hub_logo.png">
    <link rel="stylesheet" href="style.css">
    <script src="theme.js"></script>
</head>

<body class="light-mode">
    <div class="container">
        <div class="logo">
            <img src="img/social_hub_logo.png" alt="Social Hub">
        </div>
        <form class="loginForm" action="" method="POST">
            <p>Change your Password</p>
            <input type="text" name="loginId" id="loginId" value="<?= isset( $_POST['loginId'] ) ? $_POST['loginId'] : ''; ?>" placeholder="Email address or phone number">
            <?php if(isset($errors['loginId'])) { ?>
                <p class="error"><?php echo $errors['loginId']; ?></p>
            <?php } ?>

            <input type="password" title="Password must contain at least one letter, one number, one special character, and be at least 8 characters long" pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" value="<?= isset( $_POST['loginPassword'] ) ? $_POST['loginPassword'] : ''; ?>" name="loginPassword" id="loginPassword" placeholder="Password">
            <?php if(isset($errors['loginPassword'])) { ?>
                <p class="error"><?php echo $errors['loginPassword']; ?></p>
            <?php } ?>


            <input type="password" value="<?= isset( $_POST['confirmPassword'] ) ? $_POST['confirmPassword'] : ''; ?>" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password">
            <?php if(isset($errors['confirmPassword'])) { ?>
                <p class="error"><?php echo $errors['confirmPassword']; ?></p>
            <?php } ?>

            <button type="submit" id="loginBtn">Forgot Password</button>
            <div class="login_link" id="login_link">
                <a href="index.php" class="" target="">Login to Social Hub </a>
                <span> · </span>
                <a href="signup.php" class="_97w5">Sign up for Social Hub ?</a>
            </div>
        </form>
    </div>
</body>

</html>
