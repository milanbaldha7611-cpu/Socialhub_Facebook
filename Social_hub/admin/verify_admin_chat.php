<?php
session_start();
include '../_db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['action']) && $_GET['action'] == 'lock') {
    unset($_SESSION['chat_unlocked']);
    unset($_SESSION['chat_unlock_time']);
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';

    // Fetch the admin password from the admin_users table
    // For simplicity, we assume there's an 'admin' user
    $sql = "SELECT password FROM admin_users WHERE username = 'admin' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['chat_unlocked'] = true;
            $_SESSION['chat_unlock_time'] = time();
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Admin account not configured properly.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
