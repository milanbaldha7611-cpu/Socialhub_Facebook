<?php
$conn = new mysqli("localhost","root","","facebook");
$r = mysqli_query($conn, 'DESCRIBE user_data');
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['Field'] . "\n";
}
