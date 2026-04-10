<?php
$conn = new mysqli("localhost","root","","facebook");
$BOT_ID = 18;

$users = mysqli_query($conn, "SELECT srno FROM user_data WHERE srno != $BOT_ID");
$sent = 0;

while ($row = mysqli_fetch_assoc($users)) {
    $userId = $row['srno'];

    // Check if bot has already sent a message to this user
    $check = mysqli_query($conn, "SELECT msg_id FROM message WHERE sender_id = '$BOT_ID' AND receiver_id = '$userId' LIMIT 1");
    if (mysqli_num_rows($check) === 0) {
        $welcomeMsg = mysqli_real_escape_string($conn, "👋 Welcome to SocialHub! I'm your AI Bot companion. Say hi anytime and I'll reply! 🤖✨");
        $insert = "INSERT INTO message (sender_id, receiver_id, msg_contant, is_read) VALUES ('$BOT_ID', '$userId', '$welcomeMsg', 0)";
        if (mysqli_query($conn, $insert)) {
            $sent++;
        }
    }
}

echo "✅ Welcome message sent to $sent user(s).\n";
