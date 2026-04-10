<?php
require '_db_connect.php';

// Helper to execute query
function execute($conn, $sql) {
    try {
        if (!$conn->query($sql)) {
            echo "Error: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\nSQL: $sql\n";
    }
}

// 1. Insert Users (Indian Names)
echo "Seeding users...\n";
$users = [
    ['user_firstname' => 'Rahul', 'user_surname' => 'Sharma', 'user_id' => 'rahul@gmail.com', 'gender' => 'Male'],
    ['user_firstname' => 'Priya', 'user_surname' => 'Patel', 'user_id' => 'priya@gmail.com', 'gender' => 'Female'],
    ['user_firstname' => 'Amit', 'user_surname' => 'Singh', 'user_id' => 'amit@gmail.com', 'gender' => 'Male'],
    ['user_firstname' => 'Neha', 'user_surname' => 'Gupta', 'user_id' => 'neha@gmail.com', 'gender' => 'Female'],
    ['user_firstname' => 'Rohit', 'user_surname' => 'Kumar', 'user_id' => 'rohit@gmail.com', 'gender' => 'Male'],
    ['user_firstname' => 'Anjali', 'user_surname' => 'Verma', 'user_id' => 'anjali@gmail.com', 'gender' => 'Female']
];

$password = password_hash('password123', PASSWORD_DEFAULT);
$inserted_user_ids = [];

foreach ($users as $u) {
    $dob = '199' . rand(0, 9) . '-0' . rand(1, 9) . '-1' . rand(0, 9);
    $firstname = $u['user_firstname'];
    $surname = $u['user_surname'];
    $email = $u['user_id'];
    $gender = $u['gender'];

    $sql = "INSERT INTO user_data (user_firstname, user_surname, user_id, user_password, user_dob, user_gender, user_image) 
            VALUES ('$firstname', '$surname', '$email', '$password', '$dob', '$gender', 'default_avatar.png')";
    
    if ($conn->query($sql)) {
        $inserted_user_ids[] = $conn->insert_id;
    }
}

if (count($inserted_user_ids) < 2) {
    die("Not enough users inserted.\n");
}

echo "Seeding posts...\n";
// 2. Insert Posts
$captions = [
    "Enjoying a lovely evening in Mumbai! #mumbai #sunset",
    "Feeling great today! Had an amazing breakfast.",
    "Looking forward to the weekend trip to Goa.",
    "Just finished an amazing book. Highly recommended!",
    "Coffee and coding. Best combo ever.",
    "Happy Diwali everyone! Wishing you lots of joy.",
    "Throwback to my trip to Manali last year. So cold!",
    "Missing the street food of Delhi. Golgappas are life."
];

foreach ($inserted_user_ids as $uid) {
    // Each user makes 2 posts
    for ($i = 0; $i < 2; $i++) {
        $caption = $captions[array_rand($captions)];
        $sql = "INSERT INTO post (post_user_srno, post_caption, post_image, media_type) 
                VALUES ($uid, '$caption', '', 'none')";
        execute($conn, $sql);
    }
}

echo "Seeding friend requests...\n";
// 3. Friend requests and friendships
// Make user 0 friends with user 1 and user 2
$uid_0 = $inserted_user_ids[0];
$uid_1 = $inserted_user_ids[1];
$uid_2 = $inserted_user_ids[2];
$uid_3 = $inserted_user_ids[3];

// 0 & 1 are friends
execute($conn, "INSERT INTO friend_request (sender_id, receiver_id, request_status) VALUES ($uid_0, $uid_1, 'friend')");
// 0 & 2 are friends
execute($conn, "INSERT INTO friend_request (sender_id, receiver_id, request_status) VALUES ($uid_0, $uid_2, 'friend')");
// 3 sent friend request to 0
execute($conn, "INSERT INTO friend_request (sender_id, receiver_id, request_status) VALUES ($uid_3, $uid_0, 'pending')");

echo "Seeding messages...\n";
// 4. Chat messages
$messages = [
    "Hey, how are you?",
    "I am good, how about you?",
    "Doing well! What are you up to today?",
    "Just working on some code. And playing cricket later.",
    "Sounds fun! Can I join?",
    "Of course! See you at 5.",
    "Awesome!"
];

$t = time() - 3600; // start 1 hour ago
// Conversation between 0 and 1
for ($i = 0; $i < count($messages); $i++) {
    $sender = ($i % 2 == 0) ? $uid_0 : $uid_1;
    $receiver = ($i % 2 == 0) ? $uid_1 : $uid_0;
    $msg = $messages[$i];
    $date = date('Y-m-d H:i:s', $t + $i * 60);
    execute($conn, "INSERT INTO message (sender_id, receiver_id, msg_contant, is_read, msg_time) VALUES ($sender, $receiver, '$msg', '0', '$date')");
}

echo "Seeding complete!\n";
?>
