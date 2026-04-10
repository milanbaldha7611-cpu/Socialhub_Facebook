<?php
require '_db_connect.php';

$base_dest = __DIR__ . '/post_img/';

$users = [
    'rahul@gmail.com' => [
        'src' => 'C:\Users\Dell\.gemini\antigravity\brain\f8c48fa3-d656-4e48-85fd-255d1b88c3f6\rahul_profile_1772390395867.png',
        'dest' => 'rahul_profile.png'
    ],
    'priya@gmail.com' => [
        'src' => 'C:\Users\Dell\.gemini\antigravity\brain\f8c48fa3-d656-4e48-85fd-255d1b88c3f6\priya_profile_1772390421357.png',
        'dest' => 'priya_profile.png'
    ],
    'amit@gmail.com' => [
        'src' => 'C:\Users\Dell\.gemini\antigravity\brain\f8c48fa3-d656-4e48-85fd-255d1b88c3f6\amit_profile_1772390436616.png',
        'dest' => 'amit_profile.png'
    ],
    'neha@gmail.com' => [
        'src' => 'C:\Users\Dell\.gemini\antigravity\brain\f8c48fa3-d656-4e48-85fd-255d1b88c3f6\neha_profile_1772390451784.png',
        'dest' => 'neha_profile.png'
    ],
    'rohit@gmail.com' => [
        'src' => 'C:\Users\Dell\.gemini\antigravity\brain\f8c48fa3-d656-4e48-85fd-255d1b88c3f6\rohit_profile_1772390467284.png',
        'dest' => 'rohit_profile.png'
    ],
    'anjali@gmail.com' => [
        'src' => 'C:\Users\Dell\.gemini\antigravity\brain\f8c48fa3-d656-4e48-85fd-255d1b88c3f6\anjali_profile_1772390497213.png',
        'dest' => 'anjali_profile.png'
    ]
];

foreach ($users as $email => $paths) {
    if (file_exists($paths['src'])) {
        copy($paths['src'], $base_dest . $paths['dest']);
        echo "Copied image for $email \n";
        
        $filename = $paths['dest'];
        $sql = "UPDATE user_data SET user_image = '$filename' WHERE user_id = '$email'";
        if ($conn->query($sql)) {
            echo "Updated DB for $email \n";
        } else {
            echo "Error DB for $email: " . $conn->error . "\n";
        }
    } else {
        echo "File not found: " . $paths['src'] . "\n";
    }
}

echo "Image seeding complete!\n";
?>
