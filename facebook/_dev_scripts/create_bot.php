<?php
$conn = new mysqli("localhost","root","","facebook");

// --- 1. Insert the bot user (only if not already there) ---
$checkBot = mysqli_query($conn, "SELECT srno FROM user_data WHERE user_id = 'bot@socialhub.ai'");
if (mysqli_num_rows($checkBot) === 0) {
    $botPassword = password_hash('BotSecure@2024!', PASSWORD_DEFAULT);
    $insertBot = "INSERT INTO `user_data` 
        (`user_firstname`, `user_surname`, `user_id`, `user_password`, `user_dob`, `user_gender`, `user_image`, `user_role`)
        VALUES 
        ('SocialHub', 'Bot', 'bot@socialhub.ai', '$botPassword', '2000-01-01', 'Other', 'bot_avatar.png', 'user')";
    if (mysqli_query($conn, $insertBot)) {
        $botSrno = mysqli_insert_id($conn);
        echo "✅ Bot user created! Bot SRNO = $botSrno\n";
    } else {
        echo "❌ Error creating bot: " . mysqli_error($conn) . "\n";
    }
} else {
    $botRow = mysqli_fetch_assoc($checkBot);
    echo "ℹ️ Bot already exists. SRNO = " . $botRow['srno'] . "\n";
}
