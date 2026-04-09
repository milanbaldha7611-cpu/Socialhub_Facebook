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

    // Validate the login credentials
    if (empty($loginId)) {
        $errors['loginId'] = "Please enter your email address or phone number.";
    }

    if (empty($loginPassword)) {
        $errors['loginPassword'] = "Please enter your password.";
    }

    if (count($errors) === 0) {
        $sql = "SELECT * FROM user_data WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $loginId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $hashedPassword = $row['user_password'];

            if ($row['is_blocked'] == 1) {
                $errors['loginId'] = "Your account has been blocked by admin. Please contact support.";
            } else if (password_verify($loginPassword, $hashedPassword)) {
                // Store user data in session variables
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_firstname'] = $row['user_firstname'];
                $_SESSION['user_surname'] = $row['user_surname'];
                $_SESSION['user_dob'] = $row['user_dob'];
                $_SESSION['user_gender'] = $row['user_gender'];
                $_SESSION['user_create'] = $row['user_create'];
                $_SESSION['srno'] = $row['srno'];
                $_SESSION['user_image'] = $row['user_image'];

                header('Location: welcome.php');
                exit; // Always exit after redirecting
            } else {
                $errors['loginPassword'] = "Invalid password. Please try again.";
            }
        } else {
            $errors['loginId'] = "User does not exist. Please sign up.";
        }
    }
}

if (isset($_GET['pwd_success'])) {
    ?>
    <script>alert("Password Updated Successfully! You can login now.")</script>
    <?php
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="theme.js"></script>
</head>

<body class="light-mode">
    <div class="container">
        <div class="logo">
            <img src="img/social_hub_logo.png" alt="Social Hub">
        </div>
        <form class="loginForm" action="index.php" method="POST">
            <p>Log in to Social Hub</p>
            <input type="text" name="loginId" id="loginId"
                value="<?= isset($_POST['loginId']) ? $_POST['loginId'] : ''; ?>"
                placeholder="Email address or phone number">
            <?php if (isset($errors['loginId'])) { ?>
                <p class="error"><?php echo $errors['loginId']; ?></p>
            <?php } ?>
            <div class="password-field-wrapper" style="position: relative; width: 330px; margin: 10px auto 0;">
                <input type="password" value="<?= isset($_POST['loginPassword']) ? $_POST['loginPassword'] : ''; ?>"
                    name="loginPassword" id="loginPassword" placeholder="Password" style="width: 100%; margin-top: 0;">
                <i class="fas fa-eye" id="togglePassword"
                    style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; transition: color 0.3s;"></i>
            </div>
            <?php if (isset($errors['loginPassword'])) { ?>
                <p class="error" style="width: 330px; margin: 5px auto;"><?php echo $errors['loginPassword']; ?></p>
            <?php } ?>
            <button type="submit" id="loginBtn">Log in</button>
            <div class="login_link" id="login_link">
                <a href="forgot_account.php" class="" target="">Forgot Password ?</a>
                <span> · </span>
                <a href="signup.php" class="_97w5">Sign up for Social Hub ?</a>
                <br><br>
                <a href="admin/login.php" class="_97w5" style="color: var(--primary-color); font-weight: 500;"><i
                        class="fas fa-user-shield"></i> Admin Login</a>
            </div>
        </form>
        <script>
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#loginPassword');

            togglePassword.addEventListener('click', function (e) {
                // toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                // toggle the eye / eye slash icon
                this.classList.toggle('fa-eye-slash');
                this.classList.toggle('fa-eye');

                // Focus back on input
                password.focus();
            });
        </script>
    </div>
</body>

</html>