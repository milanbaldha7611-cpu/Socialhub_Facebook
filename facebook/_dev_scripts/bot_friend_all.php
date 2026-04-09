<?php
$conn = new mysqli("localhost","root","","facebook");
$BOT_ID = 18;

// Get all real users (not the bot)
$users = mysqli_query($conn, "SELECT srno FROM user_data WHERE srno != $BOT_ID");
$added = 0;
$skipped = 0;

while ($row = mysqli_fetch_assoc($users)) {
    $userId = $row['srno'];

    // Check if already friends
    $check = mysqli_query($conn, "SELECT * FROM friend_request 
        WHERE ((sender_id = '$userId' AND receiver_id = '$BOT_ID') 
            OR (sender_id = '$BOT_ID' AND receiver_id = '$userId')) 
        AND request_status = 'friend'");
    
    if (mysqli_num_rows($check) === 0) {
        // Add bot friendship
        $insert = "INSERT INTO friend_request (sender_id, receiver_id, request_status) 
                   VALUES ('$BOT_ID', '$userId', 'friend')";
        if (mysqli_query($conn, $insert)) {
            $added++;
        }
    } else {
        $skipped++;
    }
}

echo "✅ Bot befriended $added user(s). ($skipped already friends)\n";
