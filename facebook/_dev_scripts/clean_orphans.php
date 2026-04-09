<?php
require '_db_connect.php';

$queries = [
    "DELETE FROM posts WHERE post_user_srno NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM post_comment WHERE user_srno NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM post_likes WHERE user_srno NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM story WHERE user_create_by NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM friend_request WHERE sender_id NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM friend_request WHERE receiver_id NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM notifications WHERE user_srno NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM notifications WHERE response_who_show NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM chat_message WHERE sender_id NOT IN (SELECT srno FROM user_data)",
    "DELETE FROM chat_message WHERE receiver_id NOT IN (SELECT srno FROM user_data)"
];

foreach ($queries as $q) {
    try {
        if ($conn->query($q)) {
            // echo "Success\n";
        }
    } catch(Exception $e) {
        // Ignore
    }
}
echo "Done";
?>
