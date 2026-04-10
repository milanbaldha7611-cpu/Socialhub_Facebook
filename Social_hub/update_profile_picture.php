<?php
session_start();
include "_db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

    $imageFileName = $_FILES['change_image']['name'];
    $imageTempName = $_FILES['change_image']['tmp_name'];

    $userId = $_SESSION['user_id'];

    if (empty($imageFileName)) {
        $response = array('status' => 'error', 'message' => 'Please select an image.');
    } else {
        $uploadPath = 'post_img/';
        if (move_uploaded_file($imageTempName, $uploadPath . $imageFileName)) {
            $sql = "UPDATE user_data SET user_image = '$imageFileName' WHERE user_id = '$userId'";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                $_SESSION['user_image'] = $imageFileName;
                $response = array('status' => 'success', 'message' => 'Profile picture updated successfully!');
            } else {
                $response = array('status' => 'error', 'message' => 'Error updating profile picture: ' . mysqli_error($conn));
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Error uploading image.');
        }
    }

    // Send the JSON response back to the client
    header('Content-Type: application/json');
    echo json_encode($response);


?>

