<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Social Hub</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <link rel="icon" type="image/png" href="img/social_hub_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">

</head>

<body class="light-mode">
    <?php include "navbar.php";

    // Handle Post Update from Edit Popup
    if (isset($_POST['vcu_post_update_save']) && $_POST['vcu_post_update_save'] == 'Save Changes') {
        $postId = $_POST['vcu_update_post_id'];
        $caption = $_POST['vcu_post_caption'];
        $removeMedia = isset($_POST['vcu_remove_media']) ? $_POST['vcu_remove_media'] : '0';

        // Handle Media Update if provided
        if (isset($_FILES['vcu_post_media']) && !empty($_FILES['vcu_post_media']['name'])) {
            $fileName = $_FILES['vcu_post_media']['name'];
            $tempName = $_FILES['vcu_post_media']['tmp_name'];
            $fileType = $_FILES['vcu_post_media']['type'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowed = array('jpg', 'jpeg', 'png', 'gif', 'mp4');
            if (in_array($fileExt, $allowed)) {
                $newFileName = uniqid('post_') . '.' . $fileExt;
                $uploadPath = 'post_img/' . $newFileName;

                if (move_uploaded_file($tempName, $uploadPath)) {
                    $sql = "UPDATE `post` SET `post_caption`=?, `post_image`=?, `media_type`=? WHERE `post_id`=?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "sssi", $caption, $newFileName, $fileType, $postId);
                }
            }
        } else if ($removeMedia == '1') {
            $sql = "UPDATE `post` SET `post_caption`=?, `post_image`='', `media_type`='' WHERE `post_id`=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $caption, $postId);
        } else {
            $sql = "UPDATE `post` SET `post_caption`=? WHERE `post_id`=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $caption, $postId);
        }

        if (isset($stmt)) {
            mysqli_stmt_execute($stmt);
        }

        header('Location: profile.php');
        exit();
    }
    ?>
    <?php
    $userId = $_SESSION['user_id'];
    $firstName = $_SESSION['user_firstname'];
    $surname = $_SESSION['user_surname'];

    if (isset($_POST['editBtn']) && $_POST['editBtn'] == 'Save Changes') {
        $error = array();

        $firstname = $_POST['edit_firstname'];
        if ($firstname == '') {
            $error['firstname'] = 'Please enter First Name';
        }

        $surname = $_POST['edit_surname'];
        if ($surname == '') {
            $error['surname'] = 'Please enter Surname';
        }

        $id = $_POST['edit_userid'];
        if ($id == '') {
            $error['edit_userid'] = 'Please enter Mobile number or email address';
        } elseif (!(preg_match('/^\d{10}$/', $id) || filter_var($id, FILTER_VALIDATE_EMAIL))) {
            $error['edit_userid'] = 'Please provide a 10-digit mobile number or a valid email address.';
        } else {
            $checkQuery = "SELECT * FROM user_data WHERE user_id = '$id' AND user_id != '$userId'";
            $checkResult = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($checkResult) > 0) {
                $error['edit_userid'] = 'User already exists!';
            }
        }

        $dob = $_POST['edit_dob'];
        $birthdate = new DateTime($dob);
        $today = new DateTime();
        $age = $birthdate->diff($today)->y;
        if ($dob == '') {
            $error['edit_dob'] = 'Please select Date of Birth';
        } elseif ($age < 18) {
            $error['edit_dob'] = 'You must be at least 18 years old to sign up.';
        }

        $gender = $_POST['edit_gender'];
        if ($gender == '') {
            $error['edit_gender'] = 'Please select Gender';
        }

        if (count($error) === 0) {
            $sql = "UPDATE user_data SET user_firstname = '$firstname', user_surname = '$surname', user_id = '$id', user_dob = '$dob', user_gender = '$gender' WHERE user_id = '$userId'";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                $_SESSION['user_firstname'] = $firstname;
                $_SESSION['user_surname'] = $surname;
                $_SESSION['user_dob'] = $dob;
                $_SESSION['user_gender'] = $gender;
                $_SESSION['user_id'] = $userId;
                echo '<script>alert("Profile updated successfully!");</script>';
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }

    }

    ?>


    <div class="profile_container">
        <div class="profile_card">

            <!-- Animated Banner -->
            <div class="profile-banner">
                <div class="profile-banner-overlay"></div>
            </div>

            <!-- Profile Picture with Edit Overlay -->
            <div class="profile-picture-wrapper">
                <div class="profile-picture">
                    <img id="profile_img_edit" src="post_img/<?php echo $_SESSION['user_image']; ?>"
                        alt="Profile Picture">
                    <div class="profile-picture-edit-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="profile-info">
                <h2 class="profile_name">
                    <?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_surname']); ?>
                </h2>
                <p class="profile-tagline">Member of Social Hub</p>

                <!-- Stat Bar -->
                <?php
                $user_srno = $_SESSION['srno'];
                // Count Friends
                $friends_q = "SELECT COUNT(*) as friend_count FROM `friend_request` WHERE (`sender_id` = '$user_srno' OR `receiver_id` = '$user_srno') AND `request_status` = 'friend'";
                $friends_res = mysqli_query($conn, $friends_q);
                $friends_count = mysqli_fetch_assoc($friends_res)['friend_count'] ?? 0;

                // Count Posts
                $posts_q = "SELECT COUNT(*) as post_count FROM `post` WHERE `post_user_srno` = '$user_srno'";
                $posts_res = mysqli_query($conn, $posts_q);
                $posts_count = mysqli_fetch_assoc($posts_res)['post_count'] ?? 0;
                ?>
                <div class="profile-stats">
                    <div class="profile-stat-item">
                        <span class="stat-number" id="profile_friends_count"><?php echo $friends_count; ?></span>
                        <span class="stat-label">Friends</span>
                    </div>
                    <div class="profile-stat-divider"></div>
                    <div class="profile-stat-item">
                        <span class="stat-number" id="profile_posts_count"><?php echo $posts_count; ?></span>
                        <span class="stat-label">Posts</span>
                    </div>
                </div>

                <!-- Info Rows -->
                <div class="profile-info-rows">
                    <input type="hidden" name="hidden_userid" id="hidden_userid"
                        value="<?php echo $_SESSION['user_id']; ?>">

                    <div class="profile-info-row" id="profile_username_email">
                        <span class="profile-info-icon"><i class="fas fa-envelope"></i></span>
                        <span class="profile-info-label">Email</span>
                        <span class="profile-info-value"><?php echo htmlspecialchars($userId); ?></span>
                    </div>
                    <div class="profile-info-row" id="profile_username_mobile" style="display:none;">
                        <span class="profile-info-icon"><i class="fas fa-phone"></i></span>
                        <span class="profile-info-label">Mobile</span>
                        <span class="profile-info-value"><?php echo htmlspecialchars($userId); ?></span>
                    </div>
                    <div class="profile-info-row">
                        <span class="profile-info-icon"><i class="fas fa-birthday-cake"></i></span>
                        <span class="profile-info-label">Date of Birth</span>
                        <span class="profile-info-value"><?php echo htmlspecialchars($_SESSION['user_dob']); ?></span>
                    </div>
                    <div class="profile-info-row">
                        <span class="profile-info-icon"><i class="fas fa-venus-mars"></i></span>
                        <span class="profile-info-label">Gender</span>
                        <span
                            class="profile-info-value"><?php echo htmlspecialchars($_SESSION['user_gender']); ?></span>
                    </div>
                    <div class="profile-info-row">
                        <span class="profile-info-icon"><i class="fas fa-calendar-alt"></i></span>
                        <span class="profile-info-label">Joined</span>
                        <span
                            class="profile-info-value"><?php echo date('F Y', strtotime($_SESSION['user_create'])); ?></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="profile_edit">
                    <button id="profile_edit_btn" class="profile_btn">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                    <button id="change_password_btn" class="profile_btn profile_btn_secondary">
                        <i class="fas fa-lock"></i> Change Password
                    </button>
                    <button id="blocked_users_btn" class="profile_btn"
                        style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <i class="fas fa-user-slash"></i> Blocked Users
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- My Posts Section -->
    <div class="profile_container" style="margin-top: 20px;">
        <div class="profile_card"
            style="padding: 20px; background: rgba(255,255,255,0.02); border-radius: 15px; border: 1px solid rgba(128,128,128,0.1);">
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-th-large" style="color: var(--primary-color);"></i> My Posts
            </h3>
            <div id="personalPostsContainer">
                <div class="text-center p-5">
                    <i class="fas fa-circle-notch fa-spin fa-2x" style="color: var(--primary-color);"></i>
                    <p class="mt-2 text-muted">Loading your posts...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Load personal posts
            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    action: 'get_user_posts',
                    target_user_srno: '<?php echo $_SESSION['srno']; ?>'
                },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        $('#personalPostsContainer').html(res.html);
                    } else if (res.error && res.error.no_posts) {
                        $('#personalPostsContainer').html(res.error.no_posts);
                    } else {
                        $('#personalPostsContainer').html('<p class="text-center text-muted">No posts to show.</p>');
                    }
                },
                error: function () {
                    $('#personalPostsContainer').html('<p class="text-center text-danger">Error loading posts.</p>');
                }
            });
        });
    </script>

    <!-- The modal dialog -->
    <div class="modal" id="edit_profile_modal">
        <div class="section">
            <div class="modal_content">
                <div class="close_btn" id="close_edit_modal">&times;</div>
                <form action="" method="POST" id="edit_bio_form">

                    <label for="edit_firstname">FirstName:</label>
                    <input type="text" id="edit_firstname" name="edit_firstname" value="<?php echo $firstName; ?>"
                        placeholder="Enter your first name">
                    <?php

                    if (isset($error['firstname']) && !empty($error['firstname'])) {
                        echo '<div class="error-message">' . $error['firstname'] . '</div>';
                    }

                    ?>

                    <label for="edit_surname">Surname:</label>
                    <input type="text" name="edit_surname" id="edit_surname" value="<?php echo $surname; ?>"
                        placeholder="Enter your surname">
                    <?php

                    if (isset($error['surname']) && !empty($error['surname'])) {
                        echo '<div class="error-message">' . $error['surname'] . '</div>';
                    }

                    ?>

                    <label for="edit_userid">User id:</label>

                    <input type="text" name="edit_userid" id="edit_userid" value="<?php echo $userId; ?>"
                        placeholder="enter your user id">
                    <?php

                    if (isset($error['edit_userid']) && !empty($error['edit_userid'])) {
                        echo '<div class="error-message">' . $error['edit_userid'] . '</div>';
                    }

                    ?>

                    <div class="edit_dob">
                        <label for="edit_dob">Date of Birth:</label>
                        <input type="date" id="edit_dob" value="<?php echo $dob; ?>" name="edit_dob">
                        <?php

                        if (isset($error['edit_dob']) && !empty($error['edit_dob'])) {
                            echo '<div class="error-message">' . $error['edit_dob'] . '</div>';
                        }

                        ?>
                    </div>

                    <div class="edit_gender">
                        <label class="edit_gender_text">Gender :</label>
                        <input type="hidden" name="hidden_gender" id="hidden_gender" value="<?php echo $gender; ?>">
                        <div class="gender_options">
                            <span class="edit_female">
                                <label class="edit_female" for="edit_female">Female</label>
                                <input type="radio" name="edit_gender" value="Female" id="edit_female">
                            </span>
                            <span class="edit_male">
                                <label class="edit_male" for="edit_male">Male</label>
                                <input type="radio" name="edit_gender" value="Male" id="edit_male">
                            </span>
                            <span class="edit_other_gender">
                                <label class="edit_other" for="edit_other">Other</label>
                                <input type="radio" name="edit_gender" value="Other" id="edit_other">
                            </span>
                        </div>

                    </div>
                    <?php

                    if (isset($error['edit_gender']) && !empty($error['edit_gender'])) {
                        echo '<div class="error-message">' . $error['edit_gender'] . '</div>';
                    }

                    ?>
                    <input type="submit" value="Save Changes" name="editBtn" id="editBtn">
                </form>
            </div>
        </div>
    </div>

    <!-- The modal dialog for "Change Password" -->
    <div class="modal" id="change_password_modal">
        <div class="section">
            <div class="modal_content">
                <div class="close_btn" id="close_change_password_modal">&times;</div>
                <form action="" method="POST" id="change_password_form">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password"
                        placeholder="Enter current password">
                    <?php

                    if (isset($passwordError['current_password']) && !empty($passwordError['current_password'])) {
                        echo '<div class="error-message">' . $passwordError['current_password'] . '</div>';
                    }

                    ?>

                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password"
                        title="Password must contain at least one letter, one number, one special character, and be at least 8 characters long"
                        pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" name="new_password"
                        placeholder="Enter new password">

                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        placeholder="Confirm new password">
                    <?php

                    if (isset($passwordError['new_password']) && !empty($passwordError['new_password'])) {
                        echo '<div class="error-message">' . $passwordError['new_password'] . '</div>';
                    }

                    ?>

                    <input type="submit" value="Change Password" name="change_password_btn" id="change_password">
                </form>
            </div>
        </div>
    </div>

    <!-- The modal dialog for "Change Profile image" -->
    <div class="modal" id="change_img_modal">
        <div class="section">
            <div class="modal_content">
                <div class="close_btn" id="close_change_img_modal">&times;</div>
                <img id="modal_profile_img" src="post_img/<?php echo $_SESSION['user_image']; ?>" alt="Profile Picture">
                <form id="change_img_form" action="update_profile_picture.php" method="post"
                    enctype="multipart/form-data">
                    <div class="profile_image_change">
                        <label id="modal_profile_label" for="change_image">Select profile picture :</label><br>
                        <input id="change_image" name="change_image" type="file" class="preview-input"
                            data-preview="#profile_change_preview">
                        <!-- Preview Container -->
                        <div id="profile_change_preview" class="preview-box"></div>
                    </div>
                    <input type="submit" value="Change Profile picture" name="change_img_btn" id="change_img_btn">
                </form>
            </div>
        </div>
    </div>

    <!-- The modal dialog for "Blocked Users" -->
    <div class="modal" id="blocked_users_modal">
        <div class="section">
            <div class="modal_content" style="max-height: 500px; overflow-y: auto;">
                <div class="close_btn" id="close_blocked_users_modal">&times;</div>
                <h2 style="margin-bottom:20px;">Blocked Users</h2>
                <div id="blocked_users_list">
                    <?php
                    $blocker_id = $_SESSION['srno'];
                    $bu_q = "SELECT b.blocked_user_id, u.user_firstname, u.user_surname, u.user_image FROM user_blocks b JOIN user_data u ON b.blocked_user_id = u.srno WHERE b.blocker_id = '$blocker_id'";
                    $bu_res = mysqli_query($conn, $bu_q);
                    if (mysqli_num_rows($bu_res) > 0) {
                        while ($bu_row = mysqli_fetch_assoc($bu_res)) {
                            $bu_img = $bu_row['user_image'] ? $bu_row['user_image'] : 'default_user.png';
                            echo "<div class='frnd_card' style='display:flex; align-items:center; padding:10px; border-radius: 12px; margin-bottom:10px; border: 1px solid rgba(128,128,128,0.2);'>
                                    <img src='post_img/{$bu_img}' style='width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:15px;'>
                                    <div style='flex:1;'>
                                        <b>{$bu_row['user_firstname']} {$bu_row['user_surname']}</b>
                                    </div>
                                    <button class='user_unblock_btn profile_btn' data-user-srno='{$bu_row['blocked_user_id']}' style='background: var(--primary-color); color: white; padding:5px 12px; font-size:12px;'>Unblock</button>
                                  </div>";
                        }
                    } else {
                        echo "<p style='text-align:center;'>You have not blocked any users.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#blocked_users_btn').click(function () {
            $('#blocked_users_modal').css('display', 'block');
        });
        $('#close_blocked_users_modal').click(function () {
            $('#blocked_users_modal').css('display', 'none');
        });
    </script>

    <!-- Edit Post Popup -->
    <div class="vcu_popup_wrapper">
        <div class="vcu_popup_content">
            <div class="vcu_popup_heading">
                <h2>Edit Post</h2>
                <i class="fas fa-times vcu_popup_close"></i>
            </div>
            <div class="vcu_popup_form">
                <form action="profile.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="vcu_update_post_id" id="vcu_update_post_id">
                    <input type="hidden" name="vcu_remove_media" id="vcu_remove_media" value="0">

                    <div class="vcu_popup_field">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label style="margin-bottom: 0;">Current Media Preview</label>
                            <button type="button" id="vcu_remove_media_btn" class="btn btn-sm btn-outline-danger"
                                style="display: none; padding: 2px 8px; font-size: 12px; border-radius: 6px; border: 1px solid #ef4444; color: #ef4444; background: transparent; cursor: pointer; transition: 0.3s; font-weight: 500;">
                                <i class="fas fa-trash-alt me-1"></i> Remove Media
                            </button>
                        </div>
                        <div class="vcu_edit_preview" id="vcu_edit_preview_box">
                            <!-- Preview injected by JS -->
                        </div>
                    </div>

                    <div class="vcu_popup_field">
                        <label><i class="fas fa-camera-retro me-1"></i> Change Photo or Video</label>
                        <input type="file" name="vcu_post_media" id="vcu_post_media_input" class="form-control"
                            accept="image/*,video/*">
                    </div>

                    <div class="vcu_popup_field">
                        <label><i class="fas fa-quote-left me-1"></i> Post Caption</label>
                        <textarea placeholder="Tell your friends about this..." name="vcu_post_caption"
                            id="vcu_post_caption" rows="3"></textarea>
                    </div>

                    <button type="submit" name="vcu_post_update_save" value="Save Changes" id="vcu_post_update_save"
                        style="background: var(--primary-color); color: white; border: none; padding: 12px; border-radius: 10px; width: 100%; cursor: pointer; font-weight: bold; margin-top: 15px;">
                        <i class="fas fa-check-circle me-1"></i> Update Post
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Friends Section -->
    <!-- <div class="profile_container">
        <div class="sidebar_header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>My Friends</h2>
            <a href="friends.php" style="color: var(--primary-color); text-decoration: none;">See All</a>
        </div>
        <div id="friendListContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">
           <?php
           /* $my_id = $_SESSION['srno'];
            $friends_q = "SELECT * FROM friend_request WHERE (sender_id = '$my_id' OR receiver_id = '$my_id') AND request_status = 'friend'";
            $friends_res = mysqli_query($conn, $friends_q);
            if (mysqli_num_rows($friends_res) > 0) {
                while ($f_row = mysqli_fetch_assoc($friends_res)) {
                    $f_id = ($f_row['sender_id'] == $my_id) ? $f_row['receiver_id'] : $f_row['sender_id'];
                    $f_data_q = "SELECT * FROM user_data WHERE srno = '$f_id'";
                    $f_data_res = mysqli_query($conn, $f_data_q);
                    $f_data = mysqli_fetch_assoc($f_data_res);
                    if (!$f_data) continue;
                    ?>
                    <div class="frnd_card">
                        <div class="frnd_img">
                            <img src="post_img/<?php 
                                $placeholder = ($f_data['user_gender'] == 'Female') ? 'female_placeholder.png' : 'male_placeholder.png';
                                echo $f_data['user_image'] ? $f_data['user_image'] : $placeholder; 
                            ?>" alt="">
                        </div>
                        <div class="frnd_data">
                            <a class="frnd_name" href="other_user_profile.php?user_srno=<?php echo $f_id; ?>">
                                <?php echo $f_data['user_firstname'] . ' ' . $f_data['user_surname']; ?>
                            </a>
                        </div>
                        <div class="frnd_actions">
                            <a href="messanger.php?user_srno=<?php echo $f_id; ?>" class="frnd_msg_btn"><i class="fas fa-comment"></i> Message</a>
                        </div>
                    </div>*/
           //                 <?php
           //             }
           //         } else {
           //             echo "<p>No friends added yet.</p>";
           //         }
           //         ?>
    //     </div>
    // </div>


<script>
// Load profile stats (friends + posts count)
$(document).ready(function() {
    $.ajax({
        type: 'POST',
        url: 'ajax.php',
        data: { action: 'get_profile_stats' },
        dataType: 'json',
        success: function(res) {
            if (res.friends_count !== undefined) {
                $('#profile_friends_count').text(res.friends_count);
            }
            if (res.posts_count !== undefined) {
                $('#profile_posts_count').text(res.posts_count);
            }
        }
    });
});
</script>

</body>

</html>