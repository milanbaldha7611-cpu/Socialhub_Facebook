<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: welcome.php');
    exit;
}
include "_db_connect.php";

if (isset($_POST['signupBtn']) && $_POST['signupBtn'] == 'Sign Up') {

    $error = array();

    $firstname = $_POST['signup_firstname'];

    if ($firstname == '') {
        $error['firstname'] = 'Please enter First Name';
    }

    $surname = $_POST['signup_surname'];

    if ($surname == '') {
        $error['surname'] = 'Please enter Surname';
    }

    $id = $_POST['signup_id'];

    if ($id == '') {
        $error['signup_id'] = 'Please enter Mobile number or email address';
    } elseif (!(preg_match('/^\d{10}$/', $id) || filter_var($id, FILTER_VALIDATE_EMAIL))) {
        $error['signup_id'] = 'Please provide a 10-digit mobile number or a valid email address.';
    } else {
        $checkQuery = "SELECT * FROM user_data WHERE user_id = ?";

        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $checkResult = mysqli_stmt_get_result($stmt);
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $error['signup_id'] = 'User already exists!';
        }
    }

    $password = $_POST['signupPassword'];
    $connfirmPassword = $_POST['connfirmPassword'];

    if ($password == '') {
        $error['signupPassword'] = 'Please enter a New Password';
    }
    if ($connfirmPassword == '') {
        $error['connfirmPassword'] = 'Please enter a Confirm Password';
    } elseif ($password != $connfirmPassword) {
        $error['connfirmPassword'] = 'Both passwords must be same!';

    }

    $dob = $_POST['signup_dob'];
    $birthdate = new DateTime($dob);
    $today = new DateTime();
    $age = $birthdate->diff($today)->y;

    if ($dob == '') {
        $error['signup_dob'] = 'Please select Date of Birth';
    } elseif ($age < 18) {
        $error['signup_dob'] = 'You must be at least 18 years old to sign up.';
    }

    $profile_imageFileName = $_FILES['signup_image']['name'];
    $profile_imageTempName = $_FILES['signup_image']['tmp_name'];
    $profile_imageSize = $_FILES['signup_image']['size'];
    $profile_imageError = $_FILES['signup_image']['error'];

    if (empty($profile_imageFileName)) {
        $error['signup_image'] = "Please select an image.";
    } else {
        $allowedExts = array('jpg', 'jpeg', 'png', 'gif');
        $fileExt = strtolower(pathinfo($profile_imageFileName, PATHINFO_EXTENSION));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $profile_imageTempName);
        finfo_close($finfo);
        $allowedMimes = array('image/jpeg', 'image/png', 'image/gif');

        if (!in_array($fileExt, $allowedExts) || !in_array($mime_type, $allowedMimes)) {
            $error['signup_image'] = "Invalid image format. Only JPG, PNG, and GIF allowed.";
        } elseif ($profile_imageSize > 5 * 1024 * 1024) { // 5MB limit
            $error['signup_image'] = "Image size should be less than 5MB.";
        } elseif ($profile_imageError !== UPLOAD_ERR_OK) {
            $error['signup_image'] = "Error uploading image.";
        } else {
            $uploadPath = 'post_img/';
            // Assign unique name to mitigate RCE from guessing URLs and file collisions
            $newFileName = uniqid('profile_') . '.' . $fileExt;
            if (!move_uploaded_file($profile_imageTempName, $uploadPath . $newFileName)) {
                $error['signup_image'] = "Error uploading image.";
            } else {
                $profile_imageFileName = $newFileName;
            }
        }
    }

    $gender = isset($_POST['Gender']) ? $_POST['Gender'] : '';

    if ($gender == '') {
        $error['Gender'] = 'Please select Gender';
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if (count($error) === 0) {
        $sql = "INSERT INTO `user_data`(`user_firstname`, `user_surname`, `user_id`, `user_password`, `user_dob`, `user_gender`, `user_image`) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $firstname, $surname, $id, $hash, $dob, $gender, $profile_imageFileName);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Auto-friend new user with the SocialHub Bot
            $newUserSrno = mysqli_insert_id($conn);
            $BOT_ID = 18;
            $botFriendQuery = "INSERT INTO `friend_request` (`sender_id`, `receiver_id`, `request_status`) VALUES ('$BOT_ID', '$newUserSrno', 'friend')";
            mysqli_query($conn, $botFriendQuery);

            // Send welcome message from bot
            $welcomeMsg = mysqli_real_escape_string($conn, "👋 Welcome to SocialHub! I'm your AI Bot companion. Say hi anytime and I'll reply! 🤖✨");
            $welcomeInsert = "INSERT INTO `message` (`sender_id`, `receiver_id`, `msg_contant`, `is_read`) VALUES ('$BOT_ID', '$newUserSrno', '$welcomeMsg', 0)";
            mysqli_query($conn, $welcomeInsert);

            // Auto-create a welcome post for the new user
            $welcomePostCaption = mysqli_real_escape_string($conn, "🚀 I just joined SocialHub! Excited to connect with everyone here. ✨");
            $welcomePostQuery = "INSERT INTO `post` (`post_user_srno`, `post_caption`, `post_image`, `media_type`) VALUES ('$newUserSrno', '$welcomePostCaption', '', '')";
            mysqli_query($conn, $welcomePostQuery);

            header('Location: signup.php?success=1');
            exit();
        } else {
            error_log("Database Error: " . mysqli_error($conn));
            echo "<script>alert('An error occurred during registration. Please try again later.');</script>";
        }
    }
}

// Success flag passed to JS below
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup to Social Hub</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <link rel="icon" type="image/png" href="img/social_hub_logo.png">

    <link rel="stylesheet" href="style.css">
    <script src="theme.js"></script>
</head>

<body class="light-mode">
    <div class="container">
        <div class="logo">
            <img src="img/social_hub_logo.png" alt="Social Hub">
        </div>
        <form class="signupForm" action="signup.php" method="POST" enctype="multipart/form-data">

            <h3>Create a new account</h3>
            <p class="caption">It's quick and easy.</p>
            <hr>
            <input type="text" value="<?= isset($_POST['signup_firstname']) ? $_POST['signup_firstname'] : ''; ?>"
                name="signup_firstname" id="signup_firstname" placeholder="First name">
            <?php

            if (isset($error['firstname']) && !empty($error['firstname'])) {
                echo '<div class="error-message">' . $error['firstname'] . '</div>';
            }

            ?>
            <input type="text" name="signup_surname"
                value="<?= isset($_POST['signup_surname']) ? $_POST['signup_surname'] : ''; ?>" id="signup_surname"
                placeholder="Surname">
            <?php

            if (isset($error['surname']) && !empty($error['surname'])) {
                echo '<div class="error-message">' . $error['surname'] . '</div>';
            }

            ?>
            <input type="text" value="<?= isset($_POST['signup_id']) ? $_POST['signup_id'] : ''; ?>" name="signup_id"
                id="signup_id" placeholder="Mobile number or email address">
            <?php

            if (isset($error['signup_id']) && !empty($error['signup_id'])) {
                echo '<div class="error-message">' . $error['signup_id'] . '</div>';
            }

            ?>


            <div class="password-field-wrapper" style="position: relative; margin: 10px auto 0;">
                <input type="password" name="signupPassword"
                    title="Password must contain at least one letter, one number, one special character, and be at least 8 characters long"
                    pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" id="signupPassword"
                    placeholder="New password" style="width: 100%; margin-top: 0;">
                <i class="fas fa-eye" data-target="#signupPassword"
                    style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; transition: color 0.3s;"
                    onclick="togglePass(this)"></i>
            </div>
            <?php
            if (isset($error['signupPassword']) && !empty($error['signupPassword'])) {
                echo '<div class="error-message">' . $error['signupPassword'] . '</div>';
            }
            ?>

            <div class="password-field-wrapper" style="position: relative; margin: 10px auto 0;">
                <input type="password" name="connfirmPassword"
                    pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" id="connfirmPassword"
                    placeholder="Confirm password" style="width: 100%; margin-top: 0;">
                <i class="fas fa-eye" data-target="#connfirmPassword"
                    style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; transition: color 0.3s;"
                    onclick="togglePass(this)"></i>
            </div>
            <?php
            if (isset($error['connfirmPassword']) && !empty($error['connfirmPassword'])) {
                echo '<div class="error-message">' . $error['connfirmPassword'] . '</div>';
            }
            ?>
            <div class="profile_image">

                <label id="profile_label" for="signup_image">Select profile picture :</label>
                <input id="signup_image" name="signup_image" type="file" class="preview-input"
                    data-preview="#signup_preview">
                <!-- Preview Container -->
                <div id="signup_preview" class="preview-box"></div>
                <?php

                if (isset($error['signup_image']) && !empty($error['signup_image'])) {
                    echo '<div class="error-message">' . $error['signup_image'] . '</div>';
                }

                ?>
            </div>
            <div class="dob">
                <label for="signup_dob">Date of Birth:</label>
                <input type="date" value="<?= isset($_POST['signup_dob']) ? $_POST['signup_dob'] : ''; ?>"
                    id="signup_dob" name="signup_dob">
                <?php

                if (isset($error['signup_dob']) && !empty($error['signup_dob'])) {
                    echo '<div class="error-message">' . $error['signup_dob'] . '</div>';
                }

                ?>
            </div>
            <!-- <p class="gender_text">Gender :</p> -->
            <div class="gender">
                <span class="female">
                    <label class="signup_female" for="signup_female">Female</label>
                    <input type="radio" name="Gender" value="Female" <?= isset($_POST['Gender']) && $_POST['Gender'] == 'Female' ? 'checked' : ''; ?> id="signup_female">
                </span>
                <span class="male">
                    <label class="signup_male" for="signup_male">Male</label>
                    <input type="radio" name="Gender" value="Male" <?= isset($_POST['Gender']) && $_POST['Gender'] == 'Male' ? 'checked' : ''; ?> id="signup_male">
                </span>
                <span class="other_gender">
                    <label class="signup_other" for="signup_other">Other</label>
                    <input type="radio" name="Gender" <?= isset($_POST['Gender']) && $_POST['Gender'] == 'Other' ? 'checked' : ''; ?> value="Other" id="signup_other">
                </span>

            </div>
            <?php

            if (isset($error['Gender']) && !empty($error['Gender'])) {
                echo '<div class="error-message">' . $error['Gender'] . '</div>';
            }

            ?>

            <!-- <button id="signupBtn">Sign Up</button> -->
            <input type="submit" value="Sign Up" name="signupBtn" id="signupBtn">
            <div class="signup_link" id="signup_link">
                <a href="index.php" class="_97w5">Already have an account?</a>
            </div>
        </form>
    </div>

    <!-- ✅ Success Popup Modal -->
    <div id="signup_success_overlay"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.85); backdrop-filter:blur(8px); z-index:99999; align-items:center; justify-content:center;">
        <div
            style="background:#1e293b; border:1px solid rgba(255,255,255,0.12); border-radius:24px; padding:40px 36px; max-width:420px; width:90%; text-align:center; box-shadow:0 25px 60px rgba(0,0,0,0.5); position:relative; animation: popIn 0.4s ease;">
            <!-- Close X -->
            <button onclick="closeSuccessPopup()"
                style="position:absolute; top:14px; right:18px; background:none; border:none; color:#94a3b8; font-size:22px; cursor:pointer; transition:0.3s;"
                onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">&#10005;</button>

            <!-- Checkmark Icon -->
            <div
                style="width:80px; height:80px; background:linear-gradient(135deg,#22c55e,#16a34a); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; box-shadow:0 8px 25px rgba(34,197,94,0.4);">
                <i class="fas fa-check" style="color:white; font-size:36px;"></i>
            </div>

            <!-- Heading -->
            <h2 style="color:#f8fafc; font-size:1.6rem; font-weight:800; margin-bottom:10px;">🎉 Welcome Aboard!</h2>
            <p style="color:#94a3b8; font-size:0.95rem; line-height:1.6; margin-bottom:28px;">Your account has been
                created successfully.<br>You can now log in and start exploring <strong
                    style="color:#60a5fa;">SocialHub</strong>!</p>

            <!-- Go to Login Button -->
            <button onclick="closeSuccessPopup()"
                style="width:100%; background:linear-gradient(135deg,#3b82f6,#6366f1); border:none; color:white; font-size:1rem; font-weight:700; padding:14px; border-radius:12px; cursor:pointer; transition:0.3s; box-shadow:0 4px 15px rgba(59,130,246,0.35);"
                onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i> Go to Login
            </button>
        </div>
    </div>

    <style>
        @keyframes popIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>

    <script>
        function togglePass(icon) {
            const targetId = icon.getAttribute('data-target');
            const passwordInput = document.querySelector(targetId);
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            icon.classList.toggle('fa-eye-slash');
            icon.classList.toggle('fa-eye');
            passwordInput.focus();
        }

        function closeSuccessPopup() {
            document.getElementById('signup_success_overlay').style.display = 'none';
            window.location.href = 'index.php';
        }

        // Show on page load if ?success=1
        <?php if (isset($_GET['success'])): ?>
            window.addEventListener('DOMContentLoaded', function () {
                var overlay = document.getElementById('signup_success_overlay');
                overlay.style.display = 'flex';
            });
        <?php endif; ?>
    </script>
</body>

</html>