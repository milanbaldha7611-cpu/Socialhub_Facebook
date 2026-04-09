<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Social Hub</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/social_hub_logo.png">

</head>

<body class="light-mode">
    <?php include "navbar.php"; ?>
    <?php
$userSrno = $_GET['user_srno'];
//<--------Get friend profile------------->
if (isset($_GET['user_srno'])) {
    if ($_SESSION['srno'] == $userSrno) {
        header("Location: profile.php");
        exit();
    } else {
        $query = "SELECT * FROM `user_data` WHERE `srno` = '$userSrno'";
        $result = mysqli_query($conn, $query);
        $userData = mysqli_fetch_assoc($result);
    }

} else {
    echo "Invalid user ID.";
}
$logged_in_user_id = $_SESSION['srno'];
$receiver_id = $userData['srno'];

$block_check_q = "SELECT * FROM user_blocks WHERE (blocker_id = '$logged_in_user_id' AND blocked_user_id = '$userSrno') OR (blocker_id = '$userSrno' AND blocked_user_id = '$logged_in_user_id')";
$block_check_res = mysqli_query($conn, $block_check_q);
$is_blocked_by_me = false;
$is_blocked_by_them = false;

if (mysqli_num_rows($block_check_res) > 0) {
    while($b_row = mysqli_fetch_assoc($block_check_res)) {
        if ($b_row['blocker_id'] == $logged_in_user_id) $is_blocked_by_me = true;
        if ($b_row['blocker_id'] == $userSrno) $is_blocked_by_them = true;
    }
}

if ($is_blocked_by_them) {
    echo "<h2 style='text-align:center; margin-top:50px;'>Content not available.</h2>";
    exit();
}

$existing_request_query1 = "SELECT * FROM friend_request WHERE sender_id = '$logged_in_user_id' AND receiver_id = '$userSrno' AND request_status = 'pending'";
$existing_request_query2 = "SELECT * FROM friend_request WHERE sender_id = '$userSrno' AND receiver_id = '$logged_in_user_id' AND request_status = 'pending'";
$existing_request_query3 = "SELECT * FROM friend_request WHERE sender_id = '$logged_in_user_id' AND receiver_id = '$userSrno' AND request_status = 'friend'";
$existing_request_query4 = "SELECT * FROM friend_request WHERE sender_id = '$userSrno' AND receiver_id = '$logged_in_user_id' AND request_status = 'friend'";
$existing_request_result1 = mysqli_query($conn, $existing_request_query1);
$existing_request_result2 = mysqli_query($conn, $existing_request_query2);
$existing_request_result3 = mysqli_query($conn, $existing_request_query3);
$existing_request_result4 = mysqli_query($conn, $existing_request_query4);

if (mysqli_num_rows($existing_request_result3) > 0 || mysqli_num_rows($existing_request_result4) > 0) {
    // Both users have sent friend requests to each other (Friend status)
    $button_text1 = 'Remove Friend';
    $button_text2 = '';
    $button_text2_hide = 'hide';
    $request_status = 'friend';
} elseif (mysqli_num_rows($existing_request_result1) > 0) {
    // Logged-in user has sent a friend request to the profile user (Pending status)
    $button_text1 = 'Cancel Request';
    $button_text2 = '';
    $button_text2_hide = 'hide';
    $request_status = 'pending';
} elseif (mysqli_num_rows($existing_request_result2) > 0) {
    // Profile user has sent a friend request to the logged-in user (Accept & Reject status)
    $button_text1 = 'Accept';
    $button_text2 = 'Reject';
    $request_status = 'accept_reject';
} else {
    // No friend request between the users (Add Friend status)
    $button_text1 = 'Add Friend';
    $button_text2 = '';
    $button_text2_hide = 'hide';
    $request_status = 'not_sent';
}
?>


    <div class="profile_container">
        <div class="profile_card">
            <div class="profile-banner"></div>
            <div class="profile-picture">
                <img id="other_user_img" src="post_img/<?php echo $userData['user_image']; ?>" alt="Profile Picture">

            </div>
            <h2 class="other_user_name">
                <?php echo $userData['user_firstname'];
                echo " ";
                echo $userData['user_surname']; ?>
            </h2>
            <div class="profile_bio">
                <div class="profilecard_left">

                    <p class="other_user_username" id="other_user_email">Contact id :</p>
                    <p class="other_user_dob">Date of Birth :</p>
                    <p class="other_user_gender">Gender :</p>
                </div>
                <div class="profilecard_right">
                    <p class="other_user_username">
                        <?php echo $userData['user_id']; ?>
                    </p>
                    <p class="other_user_dob">
                        <?php echo $userData['user_dob']; ?>
                    </p>
                    <p class="other_user_gender">
                        <?php echo $userData['user_gender']; ?>
                    </p>
                </div>
            </div>
            <div class="profile_edit">
                <?php if ($is_blocked_by_me): ?>
                    <button class="user_unblock_btn profile_btn" style="background: var(--primary-color); color: white;" data-user-srno="<?php echo $userData['srno']; ?>">Unblock User</button>
                    <h2 style='text-align:center; margin-top:10px;'>You have blocked this user.</h2>
                <?php else: ?>
                    <button class="add_friend_btn" id="accept_frnd" data-user-srno="<?php echo $userData['srno']; ?>"
                        data-request-status="<?php echo $request_status; ?>"><?php echo $button_text1; ?></button>
                    <button class="add_friend_btn <?php echo $button_text2_hide; ?>" id="reject_frnd"
                        data-user-srno="<?php echo $userData['srno']; ?>"
                        data-request-status="<?php echo $request_status; ?>"><?php echo $button_text2; ?></button>
                    <?php if ($request_status == 'friend'): ?>
                        <a href="messanger.php?user_srno=<?php echo $userData['srno']; ?>" class="profile_btn message_btn">Message</a>
                    <?php endif; ?>
                    <button class="user_block_btn profile_btn" style="background: #ef4444; color: white;" data-user-srno="<?php echo $userData['srno']; ?>">Block User</button>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- User Posts Section -->
    <div class="profile_container" style="margin-top: 20px;">
        <div class="profile_card" style="padding: 20px; background: rgba(255,255,255,0.02); border-radius: 15px; border: 1px solid rgba(128,128,128,0.1);">
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-images" style="color: var(--primary-color);"></i> Shared Content
            </h3>
            <div id="otherUserPostsContainer">
                <div class="text-center p-5">
                    <i class="fas fa-circle-notch fa-spin fa-2x" style="color: var(--primary-color);"></i>
                    <p class="mt-2 text-muted">Loading posts...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        // Load target user's posts
        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: { 
                action: 'get_user_posts',
                target_user_srno: '<?php echo $userSrno; ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#otherUserPostsContainer').html(res.html);
                } else if (res.error && res.error.no_posts) {
                    $('#otherUserPostsContainer').html(res.error.no_posts);
                } else {
                    $('#otherUserPostsContainer').html('<p class="text-center text-muted">No posts shared yet.</p>');
                }
            },
            error: function() {
                $('#otherUserPostsContainer').html('<p class="text-center text-danger">Error loading posts.</p>');
            }
        });
    });
    </script>

</body>

</html>