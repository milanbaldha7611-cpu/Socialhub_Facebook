<?php
session_start();

if (isset($_SESSION['is_loggedin']) && $_SESSION['is_loggedin']) {
    header('location:user.php');
    exit;
}

if (isset($_POST['login'])) {
    include '../_db_connect.php';

    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $password = $_POST['password'];

    // Using password_verify securely for admins
    $sql = "SELECT * FROM `user_data` WHERE user_firstname=? AND user_role='admin'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Verify securely hashed password
        if (password_verify($password, $row['user_password'])) {
            $_SESSION['user_id'] = $row['user_srno'];
            $_SESSION['is_loggedin'] = true;
            $_SESSION['username'] = $row['user_firstname'];

            header('location:user.php');
            exit;
        } else {
            $error = "Invalid password. Please try again.";
        }
    } else {
        $error = "Admin credentials not found. Please verify your username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area Secure Login | Social Hub</title>
    <link rel="icon" type="image/png" href="../img/social_hub_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="light-mode" style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important; margin: 0; padding: 0;">
    
    <div class="container" style="width: 100%; max-width: 420px; padding: 20px;">
        <div class="logo" style="text-align: center; margin-bottom: 25px;">
            <img src="../img/social_hub_logo.png" alt="Social Hub" style="height: 80px; border-radius: 50%; box-shadow: 0 8px 25px rgba(0,0,0,0.5); border: 3px solid rgba(255,255,255,0.1);">
        </div>
        
        <form class="loginForm" action="" method="post" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); padding: 40px 30px; text-align: center;">
            <p style="font-size: 24px; font-weight: 800; color: #f8fafc; margin-bottom: 30px; letter-spacing: -0.5px;">
                <i class="fas fa-user-shield" style="color: #3b82f6; margin-right: 10px; font-size: 28px; vertical-align: middle;"></i>Admin Portal
            </p>
            
            <?php if (isset($error)) { ?>
                <p class="error" style="color: #ef4444; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 20px; text-align: left;">
                    <i class="fas fa-exclamation-circle" style="margin-right: 5px;"></i> <?php echo $error; ?>
                </p>
            <?php } ?>
            
            <div style="position: relative; margin-bottom: 15px; text-align: left;">
                <i class="fas fa-user" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px;"></i>
                <input type="text" name="name" placeholder="Admin Username" required style="width: 100%; padding: 15px 15px 15px 50px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; font-size: 16px; color: #f8fafc; outline: none; transition: border-color 0.3s; box-sizing: border-box;">
            </div>
            
            <div style="position: relative; margin-bottom: 30px; text-align: left;">
                <i class="fas fa-lock" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px;"></i>
                <input type="password" name="password" placeholder="Admin Password" required style="width: 100%; padding: 15px 15px 15px 50px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; font-size: 16px; color: #f8fafc; outline: none; transition: border-color 0.3s; box-sizing: border-box;">
            </div>
            
            <button type="submit" name="login" style="width: 100%; padding: 15px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; border-radius: 12px; font-size: 1.1rem; font-weight: 700; cursor: pointer; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3); transition: transform 0.2s, box-shadow 0.2s;">Secure Login</button>
            
            <div style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                <a href="../index.php" style="color: #94a3b8; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: color 0.2s;">
                    <i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Return to Standard Login
                </a>
            </div>
        </form>
    </div>

</body>
</html>