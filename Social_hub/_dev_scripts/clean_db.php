<?php
require '_db_connect.php';

// Keep the first user inserted for each email, delete the rest
$sql = "DELETE u1 FROM user_data u1
        INNER JOIN user_data u2 
        WHERE u1.srno > u2.srno 
        AND u1.user_id = u2.user_id";

if ($conn->query($sql)) {
    echo "Duplicate users deleted successfully.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "Done.";
?>
