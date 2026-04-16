<?php
include '_db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
)";
mysqli_query($conn, $sql);

$username = 'admin';
$password = password_hash('chat@123', PASSWORD_DEFAULT);

$check = mysqli_query($conn, "SELECT * FROM admin_users WHERE username='$username'");
if (mysqli_num_rows($check) == 0) {
    $insert = "INSERT INTO admin_users (username, password) VALUES ('$username', '$password')";
    mysqli_query($conn, $insert);
}
echo "Admin Check Setup Complete";
?>
