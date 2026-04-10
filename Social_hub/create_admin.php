<?php
include '_db_connect.php';

// First, check if an admin already exists to avoid duplicates
$check = mysqli_query($conn, "SELECT * FROM user_data WHERE user_role='admin'");
if (mysqli_num_rows($check) > 0) {
    // If an admin already exists, just update their password to the secure hash
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE user_data SET user_password='$password' WHERE user_role='admin'");
    echo "Existing admin password updated securely.";
} else {
    // If no admin exists, create one
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO user_data (user_id, user_firstname, user_surname, user_password, user_role, user_gender, user_dob) 
            VALUES ('admin@socialhub.com', 'admin', 'user', '$password', 'admin', 'Male', '1990-01-01')";
            
    if (mysqli_query($conn, $sql)) {
        echo "Admin created successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
