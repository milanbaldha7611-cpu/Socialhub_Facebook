<?php
session_start();
include "_db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$firstName = $_SESSION['user_firstname'];
$surname = $_SESSION['user_surname'];
$user_srno = $_SESSION['srno'];
$uder_image = $_SESSION['user_image'];
$loggedIn = isset($_SESSION['user_id']);

function time_ago($timestamp)
{
    if (!is_numeric($timestamp)) {
        $timestamp = strtotime($timestamp);
    }

    $difference = time() - $timestamp;

    if ($difference < 60) {
        return "Just now";
    } elseif ($difference < 3600) {
        return floor($difference / 60) . "m ago";
    } elseif ($difference < 86400) {
        return floor($difference / 3600) . "h ago";
    } elseif ($difference < 604800) {
        return floor($difference / 86400) . "d ago";
    } elseif ($difference < 31536000) {
        return floor($difference / 604800) . "w ago";
    } else {
        return floor($difference / 31536000) . "y ago";
    }
}



//<-------Add post Ajax--------->
if (isset($_POST['action']) && $_POST['action'] == 'add_post') {

    $error = array();
    $response = array();
    $caption = htmlspecialchars($_POST['caption']);
    $mediaFileName = "";
    $mediaTempName = "";
    $mediaType = "";

    if (isset($_FILES['media']) && !empty($_FILES['media']['name'])) {
        $mediaFileName = $_FILES['media']['name'];
        $mediaTempName = $_FILES['media']['tmp_name'];
        $mediaType = $_FILES['media']['type'];

        $mediaFileType = pathinfo($mediaFileName, PATHINFO_EXTENSION);
        $mediaAllowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'mp4');

        if (!in_array(strtolower($mediaFileType), $mediaAllowedTypes)) {
            $error['media'] = "Only JPG, JPEG, PNG, GIF, and MP4 files are allowed.";
        }
    } elseif (empty($caption)) {
        $error['media'] = "Please provide either a caption or media to post.";
    }


    if (empty($error)) {
        $uploadPath = 'post_img/';

        move_uploaded_file($mediaTempName, $uploadPath . $mediaFileName);

        $insertQuery = "INSERT INTO `post` (`post_user_srno`, `post_caption`, `post_image`, `media_type`) VALUES ('$user_srno', '$caption', '$mediaFileName', '$mediaType')";


        $result = mysqli_query($conn, $insertQuery);
        if (!$result) {
            $error['insert_error'] = 'Try again!';
        }
        // Create a notification for the new post
        // $notificationQuery = "INSERT INTO `notifications` (`user_srno`, `response_who_show`, `message`, `is_read`) VALUES ('$user_srno', 0, 'is added new post.', 0)";
        // mysqli_query($conn, $notificationQuery);

    }
    if (empty($error)) {
        $response['success'] = true;

    } else {
        $response['error'] = $error;
        $response['success'] = false;
    }

    echo json_encode($response);
    exit;

}


// <------Get Post Ajax------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_all_posts') {
    $user_srno = $_SESSION['srno'];

    $error = array();
    $response = array();
    $query = "SELECT * FROM `post` ORDER BY post_id DESC";
    $result = mysqli_query($conn, $query);

    // Check if there are any posts
    if (mysqli_num_rows($result) > 0) {
        ob_start();
        while ($row = mysqli_fetch_assoc($result)) {

            $post_id = $row['post_id'];
            $post_user_srno = $row['post_user_srno'];
            $post_caption = $row['post_caption'];
            $post_media = $row['post_image'];
            $post_create = $row['post_create'];
            $media_type = $row['media_type'];

            // Fetch the user data for the post author
            $post_user_query = "SELECT * FROM `user_data` WHERE `srno` = '$post_user_srno'";
            $result1 = mysqli_query($conn, $post_user_query);
            $row1 = mysqli_fetch_assoc($result1);

            // If the user who created this post was deleted, skip showing the post
            if (!$row1) {
                continue;
            }

            // Check if blocked
            $block_check_q = "SELECT 1 FROM user_blocks WHERE (blocker_id = '$user_srno' AND blocked_user_id = '$post_user_srno') OR (blocker_id = '$post_user_srno' AND blocked_user_id = '$user_srno')";
            $block_check_res = mysqli_query($conn, $block_check_q);
            if (mysqli_num_rows($block_check_res) > 0)
                continue;

            $post_user_firstname = $row1['user_firstname'];
            $post_user_surname = $row1['user_surname'];
            $post_user_srno = $row1['srno'];
            $post_user_img = $row1['user_image'];
            $post_user_profile_link = ($user_srno == $post_user_srno) ? "profile.php" : "other_user_profile.php?user_srno=" . $post_user_srno;

            // Fetch the liked user data for the post
            $checkQuery = "SELECT * FROM `post_likes` WHERE `post_id` = '$post_id' AND `user_srno` = '$user_srno'";
            $checkResult = mysqli_query($conn, $checkQuery);
            $user_like_post = mysqli_num_rows($checkResult);

            $likeCountQuery = "SELECT COUNT(*) AS like_count FROM `post_likes` WHERE `post_id` = '$post_id'";
            $likeCountResult = mysqli_query($conn, $likeCountQuery);
            $likeCountRow = mysqli_fetch_assoc($likeCountResult);
            $likeCount = $likeCountRow['like_count'];

            $likeText = ($likeCount == 1) ? "like" : "likes";
            $displayLikeText = ($likeCount > 0) ? $likeCount . ' ' . $likeText : '';

            if ($user_like_post > 0) {
                $likeIconClass = "fas fa-heart liked-red";
            } else {
                $likeIconClass = "far fa-heart";
            }

            // Fetch comments for the post
            $getCommentsQuery = "SELECT * FROM `post_comment` WHERE `post_id` = '$post_id' ORDER BY `comment_id` DESC ";
            $getCommentsResult = mysqli_query($conn, $getCommentsQuery);

            // Add post delete btn to user
            $post_update_id = '';

            if ($user_srno == $post_user_srno) {
                $deleteButton = '<input type="submit" value="Delete" name="postDelete" class="postDelete" data-postid="' . $post_id . '">';
                $post_update_id = $post_id;
            } else {
                $deleteButton = '';
            }

            ?>

            <div class="card" id="card_<?php echo $post_id; ?>" data-postid="<?php echo $post_id; ?>">
                <div class="card_head">
                    <div class="card_head_right">
                        <div class="friend_dp">
                            <a href="<?php echo $post_user_profile_link; ?>"><img src="post_img/<?php echo $post_user_img; ?>"
                                    alt=""></a>
                        </div>
                        <div class="friend_post" id="friend_post">
                            <span class="friend_name">
                                <b><a href="<?php echo $post_user_profile_link; ?>">
                                        <?php echo $post_user_firstname;
                                        echo " ";
                                        echo $post_user_surname; ?>
                                    </a>
                                </b>
                            </span>
                            <div class="post_time">
                                <?php echo time_ago($post_create); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card_head_left">
                        <?php echo $deleteButton; ?>
                        <img src="icon/3dot.png" class="update_post" data-update-id="<?= $post_update_id ?>" alt="">
                    </div>
                </div>
                <div class="card_body">
                    <div class="caption_area">
                        <p>
                            <?php echo $post_caption; ?>
                        </p>
                    </div>
                    <div class="post_media_area">
                        <?php if ($media_type === 'image/jpeg' || $media_type === 'image/png' || $media_type === 'image/gif'): ?>
                            <img src="post_img/<?php echo $post_media; ?>" alt="">
                        <?php elseif ($media_type === 'video/mp4'): ?>
                            <video controls>
                                <source src="post_img/<?php echo $post_media; ?>" type="video/mp4">
                            </video>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="like_comment">
                    <div class="like">
                        <i class="<?php echo $likeIconClass; ?> post-like-heart" style="font-size: 20px;"></i>
                        <p>Like</p>
                    </div>
                    <div class="comment">
                        <i class="far fa-comment" style="font-size: 20px;"></i>
                        <p>Comment</p>
                    </div>
                </div>
                <div class="card_footer">
                    <div class="like_count">
                        <p><b><?php echo $displayLikeText; ?></b></p>
                    </div>
                </div>
                <!-- Comment input box -->
                <div class="comment_mng_area" style="display: none;">
                    <div class="comment-input-box">
                        <form>
                            <textarea class="comment-textarea" placeholder="Write your comment"></textarea>
                            <input type="button" value="Add comment" class="add-comment-btn">
                        </form>
                    </div>

                    <div class="comments-section">
                        <?php
                        if (mysqli_num_rows($getCommentsResult) > 0) {
                            while ($commentRow = mysqli_fetch_assoc($getCommentsResult)) {
                                $commentUserSrno = $commentRow['comment_user_srno'];

                                // Skip if blocked
                                $comment_block_q = "SELECT 1 FROM user_blocks WHERE (blocker_id = '$user_srno' AND blocked_user_id = '$commentUserSrno') OR (blocker_id = '$commentUserSrno' AND blocked_user_id = '$user_srno')";
                                $comment_block_res = mysqli_query($conn, $comment_block_q);
                                if (mysqli_num_rows($comment_block_res) > 0)
                                    continue;
                                $commentText = $commentRow['comment_text'];
                                $comment_id = $commentRow['comment_id'];

                                // Fetch the user data for the comment author
                                $commentUserQuery = "SELECT * FROM `user_data` WHERE `srno` = '$commentUserSrno'";
                                $commentUserResult = mysqli_query($conn, $commentUserQuery);
                                $commentUserRow = mysqli_fetch_assoc($commentUserResult);
                                $commentUserName = $commentUserRow['user_firstname'] . " " . $commentUserRow['user_surname'];

                                // Fetch if current user liked this comment
                                $checkCommentLike = "SELECT * FROM `comment_likes` WHERE `comment_id` = '$comment_id' AND `user_srno` = '$user_srno'";
                                $commentLikeResult = mysqli_query($conn, $checkCommentLike);
                                $isCommentLiked = mysqli_num_rows($commentLikeResult) > 0 ? "active" : "";

                                // Fetch the number of likes for the comment
                                $likeCountQuery = "SELECT COUNT(*) as count FROM `comment_likes` WHERE `comment_id` = '$comment_id'";
                                $likeCountResult = mysqli_query($conn, $likeCountQuery);
                                $likeCountRow = mysqli_fetch_assoc($likeCountResult);
                                $commentLikeCount = $likeCountRow['count'];
                                $likeText = ($commentLikeCount == 1) ? "like" : "likes";
                                $displayLikeCount = ($commentLikeCount > 0) ? '<span class="comment-action-btn comment-like-count" data-commentid="' . $comment_id . '">' . $commentLikeCount . ' ' . $likeText . '</span>' : '<span class="comment-action-btn comment-like-count" data-commentid="' . $comment_id . '" style="display:none;"></span>';

                                // Display the comment (Instagram Style)
                                ?>
                                <div class="comment-card" data-comment-id="<?php echo $comment_id; ?>">
                                    <div class="comment_user_img">
                                        <img src="post_img/<?php echo $commentUserRow['user_image'] ? $commentUserRow['user_image'] : 'default_user.png'; ?>"
                                            alt="">
                                    </div>
                                    <div class="comment-user-bio">
                                        <div class="comment-user-name">
                                            <?php $comment_user_link = ($user_srno == $commentUserRow['srno']) ? "profile.php" : "other_user_profile.php?user_srno=" . $commentUserRow['srno']; ?>
                                            <a id="frnd_req_name" href="<?php echo $comment_user_link; ?>">
                                                <?php echo $commentUserName; ?>
                                            </a>
                                            <div class="comment_card_text">
                                                <p>
                                                    <?php echo $commentText; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="comment-actions">
                                            <span class="comment-action-btn">
                                                <?php echo isset($commentRow['timestamp']) ? time_ago($commentRow['timestamp']) : 'now'; ?>
                                            </span>
                                            <?php echo $displayLikeCount; ?>
                                            <?php if ($user_srno == $commentUserRow['srno']): ?>
                                                <span class="comment-action-btn commentEdit"
                                                    data-commentid="<?php echo $comment_id; ?>">Edit</span>
                                                <span class="comment-action-btn commentDelete"
                                                    data-commentid="<?php echo $comment_id; ?>">Delete</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="comment-right-actions">
                                        <i class="far fa-heart comment-like-icon <?php echo $isCommentLiked; ?>"
                                            data-commentid="<?php echo $comment_id; ?>"></i>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>

            <?php


        }
        $response['success'] = true;
        $response['html'] = ob_get_clean();
    } else {
        $response['success'] = false;
        $response['error'] = ['no_posts' => "<h2 style='text-align:center;'>No posts found.</h2>"];
    }

    echo json_encode($response);
    exit;
}

// <---------Get Specific User Posts Ajax------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_user_posts') {
    $user_srno = $_SESSION['srno'];
    $target_user_srno = $_POST['target_user_srno'];

    $query = "SELECT * FROM `post` WHERE `post_user_srno` = '$target_user_srno' ORDER BY post_id DESC";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        ob_start();
        while ($row = mysqli_fetch_assoc($result)) {
            $post_id = $row['post_id'];
            $post_user_srno = $row['post_user_srno'];
            $post_caption = $row['post_caption'];
            $post_media = $row['post_image'];
            $media_type = $row['media_type'];
            $post_create = $row['post_create'];

            $post_user_query = "SELECT * FROM `user_data` WHERE `srno` = '$post_user_srno'";
            $result1 = mysqli_query($conn, $post_user_query);
            $row1 = mysqli_fetch_assoc($result1);

            if (!$row1)
                continue;

            $post_user_firstname = $row1['user_firstname'];
            $post_user_surname = $row1['user_surname'];
            $post_user_img = $row1['user_image'];
            $post_user_profile_link = ($user_srno == $post_user_srno) ? "profile.php" : "other_user_profile.php?user_srno=" . $post_user_srno;

            // Fetch the liked user data for the post
            $checkQuery = "SELECT * FROM `post_likes` WHERE `post_id` = '$post_id' AND `user_srno` = '$user_srno'";
            $checkResult = mysqli_query($conn, $checkQuery);
            $user_like_post = mysqli_num_rows($checkResult);

            $likeCountQuery = "SELECT COUNT(*) AS like_count FROM `post_likes` WHERE `post_id` = '$post_id'";
            $likeCountResult = mysqli_query($conn, $likeCountQuery);
            $likeCountRow = mysqli_fetch_assoc($likeCountResult);
            $likeCount = $likeCountRow['like_count'];

            $likeText = ($likeCount == 1) ? "like" : "likes";
            $displayLikeText = ($likeCount > 0) ? $likeCount . ' ' . $likeText : '';

            $likeIconClass = ($user_like_post > 0) ? "fas fa-heart liked-red" : "far fa-heart";

            // Fetch comments for the post
            $getCommentsQuery = "SELECT * FROM `post_comment` WHERE `post_id` = '$post_id' ORDER BY `comment_id` DESC ";
            $getCommentsResult = mysqli_query($conn, $getCommentsQuery);

            // Add post delete btn to user
            $post_update_id = '';
            if ($user_srno == $post_user_srno) {
                $deleteButton = '<input type="submit" value="Delete" name="postDelete" class="postDelete" data-postid="' . $post_id . '">';
                $post_update_id = $post_id;
            } else {
                $deleteButton = '';
            }
            ?>
            <div class="card" id="card_<?php echo $post_id; ?>" data-postid="<?php echo $post_id; ?>">
                <div class="card_head">
                    <div class="card_head_right">
                        <div class="friend_dp">
                            <a href="<?php echo $post_user_profile_link; ?>"><img src="post_img/<?php echo $post_user_img; ?>"
                                    alt=""></a>
                        </div>
                        <div class="friend_post" id="friend_post">
                            <span class="friend_name">
                                <b><a href="<?php echo $post_user_profile_link; ?>">
                                        <?php echo $post_user_firstname;
                                        echo " ";
                                        echo $post_user_surname; ?>
                                    </a>
                                </b>
                            </span>
                            <div class="post_time">
                                <?php echo time_ago($post_create); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card_head_left">
                        <?php echo $deleteButton; ?>
                        <img src="icon/3dot.png" class="update_post" data-update-id="<?= $post_update_id ?>" alt="">
                    </div>
                </div>
                <div class="card_body">
                    <div class="caption_area">
                        <p><?php echo $post_caption; ?></p>
                    </div>
                    <div class="post_media_area">
                        <?php if (strpos($media_type, 'image') !== false): ?>
                            <img src="post_img/<?php echo $post_media; ?>" alt="">
                        <?php elseif (strpos($media_type, 'video') !== false): ?>
                            <video controls>
                                <source src="post_img/<?php echo $post_media; ?>" type="<?php echo $media_type; ?>">
                            </video>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="like_comment">
                    <div class="like">
                        <i class="<?php echo $likeIconClass; ?> post-like-heart" style="font-size: 20px;"></i>
                        <p>Like</p>
                    </div>
                    <div class="comment">
                        <i class="far fa-comment" style="font-size: 20px;"></i>
                        <p>Comment</p>
                    </div>
                </div>
                <div class="card_footer">
                    <div class="like_count">
                        <p><b><?php echo $displayLikeText; ?></b></p>
                    </div>
                </div>
                <!-- Comment area shown by default on profile as requested -->
                <div class="comment_mng_area" style="display: block;">
                    <div class="comments-section">
                        <?php
                        if (mysqli_num_rows($getCommentsResult) > 0) {
                            while ($commentRow = mysqli_fetch_assoc($getCommentsResult)) {
                                $commentUserSrno = $commentRow['comment_user_srno'];
                                // Skip if blocked
                                $comment_block_q = "SELECT 1 FROM user_blocks WHERE (blocker_id = '$user_srno' AND blocked_user_id = '$commentUserSrno') OR (blocker_id = '$commentUserSrno' AND blocked_user_id = '$user_srno')";
                                $comment_block_res = mysqli_query($conn, $comment_block_q);
                                if (mysqli_num_rows($comment_block_res) > 0)
                                    continue;

                                $commentText = $commentRow['comment_text'];
                                $comment_id = $commentRow['comment_id'];
                                $commentUserQuery = "SELECT * FROM `user_data` WHERE `srno` = '$commentUserSrno'";
                                $commentUserResult = mysqli_query($conn, $commentUserQuery);
                                $commentUserRow = mysqli_fetch_assoc($commentUserResult);
                                $commentUserName = $commentUserRow['user_firstname'] . " " . $commentUserRow['user_surname'];

                                $checkCommentLike = "SELECT * FROM `comment_likes` WHERE `comment_id` = '$comment_id' AND `user_srno` = '$user_srno'";
                                $commentLikeResult = mysqli_query($conn, $checkCommentLike);
                                $isCommentLiked = mysqli_num_rows($commentLikeResult) > 0 ? "active" : "";

                                $likeCountQuery = "SELECT COUNT(*) as count FROM `comment_likes` WHERE `comment_id` = '$comment_id'";
                                $likeCountResult = mysqli_query($conn, $likeCountQuery);
                                $commentLikeCount = mysqli_fetch_assoc($likeCountResult)['count'];
                                $likeText = ($commentLikeCount == 1) ? "like" : "likes";
                                $displayLikeCount = ($commentLikeCount > 0) ? '<span class="comment-action-btn comment-like-count" data-commentid="' . $comment_id . '">' . $commentLikeCount . ' ' . $likeText . '</span>' : '<span class="comment-action-btn comment-like-count" data-commentid="' . $comment_id . '" style="display:none;"></span>';
                                ?>
                                <div class="comment-card" data-comment-id="<?php echo $comment_id; ?>">
                                    <div class="comment_user_img">
                                        <img src="post_img/<?php echo $commentUserRow['user_image'] ? $commentUserRow['user_image'] : 'default_user.png'; ?>"
                                            alt="">
                                    </div>
                                    <div class="comment-user-bio">
                                        <div class="comment-user-name">
                                            <?php $comment_user_link = ($user_srno == $commentUserRow['srno']) ? "profile.php" : "other_user_profile.php?user_srno=" . $commentUserRow['srno']; ?>
                                            <a id="frnd_req_name"
                                                href="<?php echo $comment_user_link; ?>"><?php echo $commentUserName; ?></a>
                                            <div class="comment_card_text">
                                                <p><?php echo $commentText; ?></p>
                                            </div>
                                        </div>
                                        <div class="comment-actions">
                                            <span
                                                class="comment-action-btn"><?php echo isset($commentRow['timestamp']) ? time_ago($commentRow['timestamp']) : 'now'; ?></span>
                                            <?php if ($user_srno == $commentUserRow['srno']): ?>
                                                <span class="comment-action-btn commentEdit"
                                                    data-commentid="<?php echo $comment_id; ?>">Edit</span>
                                                <span class="comment-action-btn commentDelete"
                                                    data-commentid="<?php echo $comment_id; ?>">Delete</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
        }
        echo json_encode(['success' => true, 'html' => ob_get_clean()]);
    } else {
        echo json_encode(['success' => false, 'error' => ['no_posts' => "<h4 style='text-align:center; padding: 20px; color: #94a3b8;'>No posts shared yet.</h4>"]]);
    }
    exit;
}


//<-------delete post------->
if (isset($_POST['action']) && $_POST['action'] == 'delete_post') {
    $error = array();
    $response = array();

    $post_id = $_POST['post_id'];

    // Delete the post
    $deletePostQuery = "DELETE FROM `post` WHERE `post_id` = '$post_id'";
    $result = mysqli_query($conn, $deletePostQuery);

    // Delete associated comments
    $deleteCommentsQuery = "DELETE FROM `post_comment` WHERE `post_id` = '$post_id'";
    $resultComments = mysqli_query($conn, $deleteCommentsQuery);

    // Delete associated likes
    $deleteLikesQuery = "DELETE FROM `post_likes` WHERE `post_id` = '$post_id'";
    $resultLikes = mysqli_query($conn, $deleteLikesQuery);

    if ($result && $resultComments && $resultLikes) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
    }

    echo json_encode($response);
    exit;
}


//<-------Verify Password---->
if (isset($_POST['action']) && $_POST['action'] == 'verify_password') {
    if ($_POST['action'] === 'verify_password') {
        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];

        $checkQuery = "SELECT user_password FROM user_data WHERE user_id = '$userId'";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) === 1) {
            $row = mysqli_fetch_assoc($checkResult);
            $storedPasswordHash = $row['user_password'];

            if (password_verify($currentPassword, $storedPasswordHash)) {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateQuery = "UPDATE user_data SET user_password = '$newPasswordHash' WHERE user_id = '$userId'";
                $updateResult = mysqli_query($conn, $updateQuery);

                if ($updateResult) {
                    $response = array('success' => true, 'message' => 'Password updated successfully.');
                } else {
                    $response = array('success' => false, 'message' => 'Error updating the password.');
                }
            } else {
                $response = array('success' => false, 'message' => 'Incorrect current password.');
            }
        } else {
            $response = array('success' => false, 'message' => 'User not found.');
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}


// <------Responce friend request------------->
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'Show_Pending_frnd_req_card') {
        $logged_in_user_id = $_SESSION['srno'];

        $query = "SELECT * FROM `friend_request` WHERE `receiver_id` = $logged_in_user_id AND `request_status` = 'pending'";
        $pending_frnd_req = mysqli_query($conn, $query);
        $number_of_requests = mysqli_num_rows($pending_frnd_req);
        if ($number_of_requests > 0) {
            ob_start();
            while ($row = mysqli_fetch_assoc($pending_frnd_req)) {
                $sender_id = $row['sender_id'];
                $request_id = $row['request_id'];
                $sql_query = "SELECT * FROM `user_data` WHERE `srno` = '$sender_id'";
                $sender_data = mysqli_query($conn, $sql_query);
                $row1 = mysqli_fetch_assoc($sender_data);
                $post_user_firstname = $row1['user_firstname'];
                $post_user_surname = $row1['user_surname'];
                $post_user_srno = $row1['srno'];

                $post_user_img = $row1['user_image'];
                $post_user_gender = trim($row1['user_gender']);

                // Determine placeholder based on gender
                $placeholder = 'male_placeholder.png'; // Default to male if unknown
                if (strcasecmp($post_user_gender, 'Female') == 0) {
                    $placeholder = 'female_placeholder.png';
                } elseif (strcasecmp($post_user_gender, 'Male') == 0) {
                    $placeholder = 'male_placeholder.png';
                }
                ?>
                <div class="pen_frnd_req_card" data-sender-id="<?php echo $request_id; ?>">
                    <div class="frnd_req_img">
                        <img src="post_img/<?php echo ($post_user_img ? $post_user_img : $placeholder); ?>" alt="">
                    </div>
                    <div class="frnd_req_responce">
                        <a class="frnd_req_name" href="other_user_profile.php?user_srno=<?php echo $post_user_srno; ?>">
                            <?php echo $post_user_firstname . " " . $post_user_surname; ?>
                        </a>
                        <div class="frnd_req_actions_wrapper">
                            <div class="frnd_acc_btn_container">
                                <button class="frnd_req_acc"><i class="fas fa-user-check"></i> Accept</button>
                            </div>
                            <div class="frnd_rej_btn_container">
                                <button class="frnd_req_rej"><i class="fas fa-user-times"></i> Reject</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            $html = ob_get_clean();

            $response = array('success' => true, 'html' => $html);
            echo json_encode($response);
            exit;
        } else {
            $response = array('success' => false, 'no_req' => '<p class="no_req_msg">No request pending.</p>');
            echo json_encode($response);
            exit;
        }

    } elseif ($_POST['action'] == 'Accept_Friend_Request_pp') {
        $request_id = $_POST['request_id'];
        $query = "UPDATE friend_request SET request_status = 'friend' WHERE request_id = $request_id";
        $result = mysqli_query($conn, $query);

        if ($result) {
            // Create a notification for the user who sent the friend request
            $sender_id_query = "SELECT sender_id FROM friend_request WHERE request_id = $request_id";
            $sender_id_result = mysqli_query($conn, $sender_id_query);
            $sender_id_row = mysqli_fetch_assoc($sender_id_result);
            $sender_id = $sender_id_row['sender_id'];

            $notification_query = "INSERT INTO notifications (`user_srno`, `response_who_show`, `message`, `is_read`) VALUES ('$user_srno', '$sender_id', 'has accepted your friend request.', 0)";
            mysqli_query($conn, $notification_query);

            // Delete related notifications 
            $delete_related_notifications_query = "DELETE FROM notifications WHERE `user_srno` = '$sender_id' AND `response_who_show` = $user_srno";
            mysqli_query($conn, $delete_related_notifications_query);

            echo json_encode(['success' => true, 'message' => 'Friend request accepted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to accept friend request.']);
        }
    } elseif ($_POST['action'] == 'Reject_Friend_Request_pp') {
        $request_id = $_POST['request_id'];
        $query = "DELETE FROM friend_request WHERE request_id = $request_id";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Friend request rejected.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject friend request.']);
        }
        ;
    }
}


// <------Sent friend request on page other user profile------------->
if ($_POST['action'] == 'Send_Friend_Request') {
    $receiver_id = $_POST['receiver_id'];
    $sender_id = $_SESSION['srno'];

    $insert_query = "INSERT INTO friend_request (sender_id, receiver_id, request_status) VALUES ('$sender_id', '$receiver_id', 'pending')";
    $insert_result = mysqli_query($conn, $insert_query);

    if ($insert_result) {
        // Create a notification for the user who received the friend request
        $notification_query = "INSERT INTO notifications (user_srno, response_who_show, message, is_read) VALUES ('$sender_id', '$receiver_id', 'has sent you friend request.', 0)";
        mysqli_query($conn, $notification_query);

        $response = array('success' => true, 'message' => 'Friend request sent successfully.');
        echo json_encode($response);
        exit;
    } else {
        $response = array('success' => false, 'message' => 'Failed to send friend request.');
        echo json_encode($response);
        exit;
    }
}


// <------Accept friend request on page other user profile------------->
if ($_POST['action'] == 'Accept_Friend_Request') {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_SESSION['srno'];

    $update_query = "UPDATE friend_request SET request_status = 'friend' WHERE (sender_id = '$sender_id' AND receiver_id = '$receiver_id') OR (sender_id = '$receiver_id' AND receiver_id = '$sender_id')";
    $update_result = mysqli_query($conn, $update_query);

    if ($update_result) {
        // Notification for the request response
        $receiver_notification_query = "INSERT INTO notifications (`user_srno`, `response_who_show`, `message`, `is_read`) VALUES ('$receiver_id', '$sender_id', 'has accepted your friend request.', 0)";
        mysqli_query($conn, $receiver_notification_query);

        // Delete related notifications with the message "is sent you a friend request"
        $delete_related_notifications_query = "DELETE FROM notifications WHERE `user_srno` = '$sender_id' AND `response_who_show` = $receiver_id";
        mysqli_query($conn, $delete_related_notifications_query);

        $response = array('success' => true, 'message' => 'Friend request accepted.');
        echo json_encode($response);
        exit;
    } else {
        $response = array('success' => false, 'message' => 'Failed to accept friend request.');
        echo json_encode($response);
        exit;
    }
}


// <------Reject friend request on page other user profile------------->
if ($_POST['action'] == 'Reject_Friend_Request') {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_SESSION['srno'];

    // Delete the friend request row from the friend_request table
    $delete_query = "DELETE FROM friend_request WHERE (sender_id = '$sender_id' AND receiver_id = '$receiver_id') OR (sender_id = '$receiver_id' AND receiver_id = '$sender_id')";
    $delete_result = mysqli_query($conn, $delete_query);

    if ($delete_result) {
        $response = array('success' => true, 'message' => 'Friend request rejected.');
        echo json_encode($response);
        exit;
    } else {
        $response = array('success' => false, 'message' => 'Failed to reject friend request.');
        echo json_encode($response);
        exit;
    }
}


// <------Block User Functionality------------->
if ($_POST['action'] == 'block_user') {
    $blocked_user_id = $_POST['user_id'];
    $blocker_id = $_SESSION['srno'];

    // Prevent duplicate block
    $check_query = "SELECT * FROM user_blocks WHERE blocker_id = '$blocker_id' AND blocked_user_id = '$blocked_user_id'";
    $check_res = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_res) == 0) {
        $insert_query = "INSERT INTO user_blocks (blocker_id, blocked_user_id) VALUES ('$blocker_id', '$blocked_user_id')";
        $insert_result = mysqli_query($conn, $insert_query);

        // Stopping deletion of friendships to allow "restoration" of data on unblock
        // $del_query = "DELETE FROM friend_request WHERE (sender_id = '$blocker_id' AND receiver_id = '$blocked_user_id') OR (sender_id = '$blocked_user_id' AND receiver_id = '$blocker_id')";
        // mysqli_query($conn, $del_query);

        if ($insert_result) {
            echo json_encode(['success' => true, 'message' => 'User blocked.']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to block user.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User is already blocked.']);
        exit;
    }
}

// <------Unblock User Functionality------------->
if ($_POST['action'] == 'unblock_user') {
    $blocked_user_id = $_POST['user_id'];
    $blocker_id = $_SESSION['srno'];

    $delete_query = "DELETE FROM user_blocks WHERE blocker_id = '$blocker_id' AND blocked_user_id = '$blocked_user_id'";
    $delete_result = mysqli_query($conn, $delete_query);

    if ($delete_result) {
        echo json_encode(['success' => true, 'message' => 'User unblocked.']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to unblock user.']);
        exit;
    }
}

// <------Cancel/Remove friend request on page other user profile------------->
if ($_POST['action'] == 'Cancel_Friend_Request_Or_Remove_Friend') {
    $receiver_id = $_POST['receiver_id'];
    $sender_id = $_SESSION['srno'];

    // Check if a friend request exists between the sender and receiver
    $existing_request_query = "SELECT * FROM friend_request WHERE (sender_id = '$sender_id' AND receiver_id = '$receiver_id') OR (sender_id = '$receiver_id' AND receiver_id = '$sender_id')";
    $existing_request_result = mysqli_query($conn, $existing_request_query);

    if (mysqli_num_rows($existing_request_result) > 0) {
        // Delete the friend request row from the friend_request table
        $delete_query = "DELETE FROM friend_request WHERE (sender_id = '$sender_id' AND receiver_id = '$receiver_id') OR (sender_id = '$receiver_id' AND receiver_id = '$sender_id')";
        $delete_result = mysqli_query($conn, $delete_query);

        if ($delete_result) {
            $response = array('success' => true, 'message' => 'Friend request canceled or friend removed.');
            echo json_encode($response);
            exit;
        } else {
            $response = array('success' => false, 'message' => 'Failed to cancel friend request or remove friend.');
            echo json_encode($response);
            exit;
        }
    } else {
        $response = array('success' => false, 'message' => 'No friend request found between the users.');
        echo json_encode($response);
        exit;
    }
}


// <---------Like Functionality------------->
if (isset($_POST['action']) && $_POST['action'] == 'like_post') {
    $error = array();
    $response = array();

    // Get data from the AJAX request
    $postId = $_POST['post_id'];
    $user_srno = $_SESSION['srno'];

    // Check if the user has already liked the post
    $checkQuery = "SELECT * FROM `post_likes` WHERE `post_id` = '$postId' AND `user_srno` = '$user_srno'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) === 0) {
        // Insert the like into the post_likes table
        $insertQuery = "INSERT INTO `post_likes` (`post_id`, `user_srno`) VALUES ('$postId', '$user_srno')";
        $insertResult = mysqli_query($conn, $insertQuery);

        if ($insertResult) {
            // Get the updated like count for the post
            $likeCountQuery = "SELECT COUNT(*) AS like_count FROM `post_likes` WHERE `post_id` = '$postId'";
            $likeCountResult = mysqli_query($conn, $likeCountQuery);
            $likeCountRow = mysqli_fetch_assoc($likeCountResult);
            $likeCount = $likeCountRow['like_count'];

            $response['success'] = true;
            $response['likeCount'] = $likeCount;
            $response['action'] = 'liked';
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to like the post.';
        }
    } else {
        // Remove the like from the post_likes table
        $deleteQuery = "DELETE FROM `post_likes` WHERE `post_id` = '$postId' AND `user_srno` = '$user_srno'";
        $deleteResult = mysqli_query($conn, $deleteQuery);

        if ($deleteResult) {
            // Get the updated like count for the post
            $likeCountQuery = "SELECT COUNT(*) AS like_count FROM `post_likes` WHERE `post_id` = '$postId'";
            $likeCountResult = mysqli_query($conn, $likeCountQuery);
            $likeCountRow = mysqli_fetch_assoc($likeCountResult);
            $likeCount = $likeCountRow['like_count'];

            $response['success'] = true;
            $response['likeCount'] = $likeCount;
            $response['action'] = 'unliked';
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to remove the like.';
        }
    }

    echo json_encode($response);
    exit;
}


// <---------Add Comment Functionality------------->
if (isset($_POST['action']) && $_POST['action'] == 'add_comment') {
    $error = array();
    $response = array();

    // Get data from the AJAX request
    $postId = $_POST['post_id'];
    $commentText = htmlspecialchars($_POST['comment_text']);
    $userSrno = $_SESSION['srno'];

    // Insert the comment into the post_comment table
    $insertQuery = "INSERT INTO `post_comment` (`post_id`, `comment_user_srno`, `comment_text`) VALUES ('$postId', '$userSrno', '$commentText')";
    $insertResult = mysqli_query($conn, $insertQuery);

    $commentId = mysqli_insert_id($conn);

    if ($insertResult) {
        // Fetch the user data for the comment author
        $commentUserQuery = "SELECT * FROM `user_data` WHERE `srno` = '$userSrno'";
        $commentUserResult = mysqli_query($conn, $commentUserQuery);
        $commentUserRow = mysqli_fetch_assoc($commentUserResult);
        $commentUserName = $commentUserRow['user_firstname'] . " " . $commentUserRow['user_surname'];

        $postOwnerQuery = "SELECT post_user_srno FROM post WHERE post_id = '$postId'";
        $postOwnerResult = mysqli_query($conn, $postOwnerQuery);
        $postOwnerRow = mysqli_fetch_assoc($postOwnerResult);
        $postOwnerSrno = $postOwnerRow['post_user_srno'];

        $notificationMessage = 'commented on your post: ' . $commentText;
        $insertNotificationQuery = "INSERT INTO notifications (`user_srno`, `response_who_show`, `message`, `is_read`) VALUES ('$userSrno', '$postOwnerSrno', 'is comment on your post.', 0)";
        mysqli_query($conn, $insertNotificationQuery);

        $response['success'] = true;
        ob_start();
        ?>
        <div class="comment-card" data-comment-id="<?= $commentId ?>">
            <div class="comment_user_img">
                <img src="post_img/<?php echo $_SESSION['user_image']; ?>" alt="">
            </div>
            <div class="comment-user-bio">
                <div class="comment-user-name">
                    <a id="frnd_req_name" href="profile.php">
                        <?php echo $commentUserName; ?>
                    </a>
                    <div class="comment_card_text">
                        <p><?php echo $commentText; ?></p>
                    </div>
                </div>
                <div class="comment-actions">
                    <span class="comment-action-btn">Just now</span>
                    <span class="comment-action-btn comment-like-count" data-commentid="<?= $commentId ?>"
                        style="display:none;"></span>
                    <span class="comment-action-btn commentEdit" data-commentid="<?= $commentId ?>">Edit</span>
                    <span class="comment-action-btn commentDelete" data-commentid="<?= $commentId ?>">Delete</span>
                </div>
            </div>
            <div class="comment-right-actions">
                <i class="far fa-heart comment-like-icon" data-commentid="<?= $commentId ?>"></i>
            </div>
        </div>
        <?php
        $html_comment = ob_get_clean();
        $response['html_comment'] = $html_comment;
    } else {
        $response['success'] = false;
    }

    echo json_encode($response);
    exit;
}


// <---------Delete Comment Functionality------------->
if (isset($_POST['action']) && $_POST['action'] == 'delete_comment') {
    $error = array();
    $response = array();

    // Get data from the AJAX request
    $commentId = $_POST['comment_id'];

    // Delete the comment from the post_comment table
    $deleteQuery = "DELETE FROM `post_comment` WHERE `comment_id` = '$commentId'";
    $deleteResult = mysqli_query($conn, $deleteQuery);

    if ($deleteResult) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
    }

    echo json_encode($response);
    exit;
}


// <---------Friends List Functionality------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_friend_list') {
    $user_id = $_SESSION['srno'];

    $query1 = "SELECT * FROM `friend_request` WHERE `sender_id` = ? AND `request_status` = 'friend' AND `receiver_id` NOT IN (SELECT blocked_user_id FROM user_blocks WHERE blocker_id = ?) AND `receiver_id` NOT IN (SELECT blocker_id FROM user_blocks WHERE blocked_user_id = ?)";
    $stmt1 = mysqli_prepare($conn, $query1);
    mysqli_stmt_bind_param($stmt1, "iii", $user_id, $user_id, $user_id);
    mysqli_stmt_execute($stmt1);
    $result1 = mysqli_stmt_get_result($stmt1);

    $response = array();

    while ($row = mysqli_fetch_assoc($result1)) {
        $friend_user_id = $row['receiver_id'];
        $request_id = $row['request_id'];
        $friend_detail_q = "SELECT * FROM `user_data` WHERE `srno` = ?";
        $stmt_frnd_det = mysqli_prepare($conn, $friend_detail_q);
        mysqli_stmt_bind_param($stmt_frnd_det, "i", $friend_user_id);
        mysqli_stmt_execute($stmt_frnd_det);
        $result_frnd_det = mysqli_stmt_get_result($stmt_frnd_det);
        $row1 = mysqli_fetch_assoc($result_frnd_det);

        $friend_fristname = $row1['user_firstname'];
        $friend_surname = $row1['user_surname'];
        $friend_id = $row1['srno'];
        $friend_image = $row1['user_image'];

        $friend_gender = $row1['user_gender'];
        $placeholder = ($friend_gender == 'Female') ? 'female_placeholder.png' : 'male_placeholder.png';

        $html_friend1 = '<div class="frnd_card" data-request-id="' . $request_id . '" data-friend-id="' . $friend_id . '">
            <div class="frnd_img">
                <img src="post_img/' . ($friend_image ? $friend_image : $placeholder) . '" alt="">
            </div>
            <div class="frnd_data">
                <a class="frnd_name" href="other_user_profile.php?user_srno=' . $friend_id . '">
                    ' . $friend_fristname . ' ' . $friend_surname . '
                </a>
            </div>
            <div class="frnd_actions">
                <a href="messanger.php?user_srno=' . $friend_id . '" class="frnd_msg_btn"><i class="fas fa-comment"></i> Message</a>
                <button class="frnd_remove_btn"><i class="fas fa-user-minus"></i> Remove</button>
            </div>
        </div>';

        $response['html_friend1'][] = $html_friend1;
    }

    $query2 = "SELECT * FROM `friend_request` WHERE `receiver_id` = ? AND `request_status` = 'friend' AND `sender_id` NOT IN (SELECT blocked_user_id FROM user_blocks WHERE blocker_id = ?) AND `sender_id` NOT IN (SELECT blocker_id FROM user_blocks WHERE blocked_user_id = ?)";
    $stmt2 = mysqli_prepare($conn, $query2);
    mysqli_stmt_bind_param($stmt2, "iii", $user_id, $user_id, $user_id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);

    while ($row = mysqli_fetch_assoc($result2)) {
        $friend_user_id = $row['sender_id'];
        $request_id = $row['request_id'];

        $friend_detail_q = "SELECT * FROM `user_data` WHERE `srno` = ?";
        $stmt_frnd_det = mysqli_prepare($conn, $friend_detail_q);
        mysqli_stmt_bind_param($stmt_frnd_det, "i", $friend_user_id);
        mysqli_stmt_execute($stmt_frnd_det);
        $result_frnd_det = mysqli_stmt_get_result($stmt_frnd_det);
        $row1 = mysqli_fetch_assoc($result_frnd_det);

        $friend_fristname = $row1['user_firstname'];
        $friend_surname = $row1['user_surname'];
        $friend_id = $row1['srno'];
        $friend_image = $row1['user_image'];

        $friend_gender = $row1['user_gender'];
        $placeholder = ($friend_gender == 'Female') ? 'female_placeholder.png' : 'male_placeholder.png';

        $html_friend2 = '<div class="frnd_card" data-request-id="' . $request_id . '" data-friend-id="' . $friend_id . '">
            <div class="frnd_img">
                <img src="post_img/' . ($friend_image ? $friend_image : $placeholder) . '" alt="">
            </div>
            <div class="frnd_data">
                <a class="frnd_name" href="other_user_profile.php?user_srno=' . $friend_id . '">
                    ' . $friend_fristname . ' ' . $friend_surname . '
                </a>
            </div>
            <div class="frnd_actions">
                <a href="messanger.php?user_srno=' . $friend_id . '" class="frnd_msg_btn"><i class="fas fa-comment"></i> Message</a>
                <button class="frnd_remove_btn"><i class="fas fa-user-minus"></i> Remove</button>
            </div>
        </div>';

        $response['html_friend2'][] = $html_friend2;
    }

    echo json_encode($response);
    exit;
}


// <---------Remove friend from friends.php ------------->
if (isset($_POST['action']) && $_POST['action'] == 'remove_frnd_frndlst') {
    $requestId = $_POST['requestId'];

    $query = "DELETE FROM `friend_request` WHERE `request_id` = $requestId";
    $stmt = mysqli_query($conn, $query);

    if ($stmt) {
        // Friend removed successfully
        $response['success'] = true;
    } else {
        // Error occurred while removing the friend
        $response['success'] = false;
        $response['message'] = 'Error: Unable to remove friend.';
    }

    echo json_encode($response);
    exit;
}


// <---------Search friends from friends.php ------------->
if (isset($_POST['action']) && $_POST['action'] == 'search_friends') {
    $user_id = $_SESSION['srno'];
    $search_query = $_POST['searchQuery'];

    $query = "SELECT * FROM `user_data` WHERE (`user_firstname` LIKE ? OR `user_surname` LIKE ?) AND `srno` IN (
        SELECT `receiver_id` FROM `friend_request` WHERE `sender_id` = ? AND `request_status` = 'friend'
        UNION
        SELECT `sender_id` FROM `friend_request` WHERE `receiver_id` = ? AND `request_status` = 'friend'
    ) AND `srno` NOT IN (SELECT blocked_user_id FROM user_blocks WHERE blocker_id=?) AND `srno` NOT IN (SELECT blocker_id FROM user_blocks WHERE blocked_user_id=?)";

    $stmt = mysqli_prepare($conn, $query);
    $search_param = "%{$search_query}%";
    mysqli_stmt_bind_param($stmt, "ssiiii", $search_param, $search_param, $user_id, $user_id, $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $filteredFriends = array();
    while ($row = mysqli_fetch_assoc($result)) {
        // Create HTML for filtered friend cards
        $friend_id = $row['srno'];
        $friend_firstname = $row['user_firstname'];
        $friend_surname = $row['user_surname'];
        $friend_image = $row['user_image'];

        $html_friend = '<div class="frnd_card" data-friend-id="' . $friend_id . '">
            <div class="frnd_img">
                <img src="post_img/' . ($friend_image ? $friend_image : 'default_user.png') . '" alt="">
            </div>
            <div class="frnd_data">
                <a class="frnd_name" href="other_user_profile.php?user_srno=' . $friend_id . '">
                    ' . $friend_firstname . ' ' . $friend_surname . '
                </a>
                <div class="frnd_actions">
                    <a href="messanger.php?user_srno=' . $friend_id . '" class="frnd_msg_btn"><i class="fas fa-comment"></i> Message</a>
                    <button class="frnd_remove_btn" data-friend-id="' . $friend_id . '"><i class="fas fa-user-minus"></i> Remove</button>
                </div>
            </div>
        </div>';

        $filteredFriends[] = $html_friend;
    }

    $response['filteredFriends'] = $filteredFriends;
    echo json_encode($response);
    exit;
}


// <---------get friend data for chat-header------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_friend_details') {
    if (isset($_POST['friendId'])) {
        $friendId = $_POST['friendId'];

        $friendQuery = "SELECT * FROM `user_data` WHERE srno = $friendId";
        $friendResult = mysqli_query($conn, $friendQuery);
        $friendRow = mysqli_fetch_assoc($friendResult);
        $response = '<div class="chat-header-content">
                            <button id="chat-back-btn" class="chat-back-btn">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <img class="Chat_frnd_dp" src="post_img/' . $friendRow['user_image'] . '" alt="avatar" />
                            <div class="chat-about">
                                <div class="chat-with" data-friend-id = "' . $friendRow['srno'] . '">' . $friendRow['user_firstname'] . ' ' . $friendRow['user_surname'] . '</div>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <button class="user_block_btn profile_btn" data-user-srno="' . $friendRow['srno'] . '" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 5px 10px; font-size: 14px; border-radius: 8px;">
                                <i class="fas fa-ban"></i> Block
                            </button>
                            <button id="clear_chat">
                                <img src="icon/delete-button.png" alt="">
                            </button>
                        </div>';
        echo json_encode($response);
    }
}


// <---------get group data for chat-header------------->
// if (isset($_POST['action']) && $_POST['action'] == 'get_group_details') {
//     if (isset($_POST['groupId'])) {
//         $groupId = $_POST['groupId'];

//         $groupQuery = "SELECT * FROM `chat_groups` WHERE group_id = $groupId";
//         $groupResult = mysqli_query($conn, $groupQuery);
//         $groupRow = mysqli_fetch_assoc($groupResult);

//         $response = '<div>
//                         <img class="Chat_frnd_dp" src="' . $groupRow['group_image'] . '" alt="group-icon" />
//                         <div class="chat-about">
//                             <div class="chat-with" data-group-id="' . $groupRow['group_id'] . '">' . $groupRow['group_name'] . '</div>
//                         </div>
//                         </div>

//                         <button  id="clear_chat">
//                             <img src="icon/delete-button.png" alt="">
//                         </button>

//                         ';

//         echo json_encode($response);
//     }
// }


// <---------Send message to database ------------->
if (isset($_POST['action']) && $_POST['action'] == 'send_message') {
    $senderId = $_SESSION['srno'];
    $receiverId = $_POST['receiverId'];
    $message = htmlspecialchars($_POST['message']);

    $insertQuery = "INSERT INTO `message` (`sender_id`, `receiver_id`, `msg_contant`, `is_read`) VALUES ('$senderId', '$receiverId', '$message', 0)";
    if (mysqli_query($conn, $insertQuery)) {

        // ==== BOT AUTO-REPLY ====
        $BOT_ID = 18; // Bot's user srno
        if ((int) $receiverId === $BOT_ID) {
            $msgLower = strtolower($message);

            // Keyword-based smart replies
            if (preg_match('/\b(hi|hello|hey|hii|helo|howdy)\b/', $msgLower)) {
                $botReply = "👋 Hello! I'm SocialHub Bot. How can I help you today?";
            } elseif (preg_match('/\b(how are you|how r u|how do you do|how are u)\b/', $msgLower)) {
                $botReply = "😊 I'm doing great, thanks for asking! I'm always here to chat. How about you?";
            } elseif (preg_match('/\b(bye|goodbye|see you|cya|ttyl)\b/', $msgLower)) {
                $botReply = "👋 Goodbye! Feel free to come back anytime. Take care! 😊";
            } elseif (preg_match('/\b(thank|thanks|thx|ty)\b/', $msgLower)) {
                $botReply = "🙏 You're most welcome! Is there anything else I can help with?";
            } elseif (preg_match('/\b(help|assist|support)\b/', $msgLower)) {
                $botReply = "🤖 Sure! I can chat with you, answer questions, or just keep you company. What do you need?";
            } elseif (preg_match('/\b(joke|funny|laugh|humor)\b/', $msgLower)) {
                $jokes = [
                    "😂 Why don't scientists trust atoms? Because they make up everything!",
                    "😄 Why did the scarecrow win an award? Because he was outstanding in his field!",
                    "🤣 I told my wife she was drawing her eyebrows too high. She looked surprised!",
                    "😂 Why don't eggs tell jokes? They'd crack each other up!",
                ];
                $botReply = $jokes[array_rand($jokes)];
            } elseif (preg_match('/\b(time|date|day|today)\b/', $msgLower)) {
                $botReply = "🕒 Right now it's " . date('l, F j, Y \a\t g:i A') . " (server time).";
            } elseif (preg_match('/\b(weather|rain|sunny|hot|cold|temperature)\b/', $msgLower)) {
                $botReply = "🌤️ I can't check live weather, but I hope it's lovely where you are! Stay comfortable 😊";
            } elseif (preg_match('/\b(love|like you|miss you)\b/', $msgLower)) {
                $botReply = "❤️ Aww, that's so sweet! I'm just a bot, but I care about making your day better! 😊";
            } elseif (preg_match('/\b(name|who are you|what are you)\b/', $msgLower)) {
                $botReply = "🤖 I'm SocialHub Bot, your friendly AI companion on this platform! I'm here to chat and help you out.";
            } elseif (preg_match('/\bhow old\b/', $msgLower)) {
                $botReply = "🤖 I was born when this platform was built! I don't age — I only get smarter 😄";
            } elseif (preg_match('/\b(sad|depressed|upset|unhappy|bad|not good)\b/', $msgLower)) {
                $botReply = "😢 Oh no, I'm sorry to hear that! Remember, tough times don't last. You've got this! 💪 I'm here if you want to talk.";
            } elseif (preg_match('/\b(good|great|awesome|amazing|fantastic|happy|fine)\b/', $msgLower)) {
                $botReply = "🎉 That's wonderful to hear! Keep that positive energy going! 😊";
            } elseif (preg_match('/\b(what is|whats|tell me about|explain)\b/', $msgLower)) {
                $botReply = "🤔 Interesting question! I'm a chat bot, so I have limited knowledge. But I'm here to try my best! 😊";
            } else {
                $defaultReplies = [
                    "🤖 Interesting! Tell me more 😊",
                    "💬 I hear you! How can I help?",
                    "😊 That's cool! What else is on your mind?",
                    "🤔 Hmm, let me think... Just kidding, I'm a bot! 😂 But I'm here to chat!",
                    "✨ I'm still learning, but I love our conversations!",
                    "😄 Thanks for chatting with me! What else would you like to talk about?",
                ];
                $botReply = $defaultReplies[array_rand($defaultReplies)];
            }

            // Insert bot's auto-reply
            $botMsg = mysqli_real_escape_string($conn, $botReply);
            $botInsert = "INSERT INTO `message` (`sender_id`, `receiver_id`, `msg_contant`, `is_read`) VALUES ('$BOT_ID', '$senderId', '$botMsg', 0)";
            mysqli_query($conn, $botInsert);
        }
        // ==== END BOT ====

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error inserting message']);
    }
}


// <---------Show message history to database ------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_messages') {

    $userId = $_SESSION['srno'];

    $friendId = $_POST['friendId'];

    // Retrieve messages from the database based on the user and friend IDs
    $messagesQuery = "SELECT * FROM `message` WHERE (sender_id = '$userId' AND receiver_id = '$friendId') OR (sender_id = '$friendId' AND receiver_id = '$userId') ORDER BY msg_time ASC";
    $messagesResult = mysqli_query($conn, $messagesQuery);
    $message = '';
    while ($row = mysqli_fetch_assoc($messagesResult)) {
        if ($userId == $row['sender_id']) {
            if ($row['is_read'] == 0) {
                $messageReadStatus = 'my-message-unread';
            } else {
                $messageReadStatus = 'my-message';
            }
            $message .= '<li style="width: 100%; display: block; clear: both; margin-bottom: 10px;">
                            <div class="message-data" id="user_message" data-message-id ="' . $row['msg_id'] . '" style="text-align: right; margin-bottom: 5px;">
                                <span class="message-data-time">' . $row['msg_time'] . '</span>
                            </div>
                            <div class="message ' . $messageReadStatus . '" style="float: right; clear: both;">' . $row['msg_contant'] . '</div>
                        </li>';
        } else {
            // For messages sent by the friend, update is_read to 1
            $messageId = $row['msg_id'];
            $updateReadStatusQuery = "UPDATE `message` SET `is_read` = 1 WHERE `msg_id` = '$messageId'";
            mysqli_query($conn, $updateReadStatusQuery);

            $message .= '<li id="friend_message" data-message-id ="' . $row['msg_id'] . '" style="width: 100%; display: block; clear: both; margin-bottom: 10px;">
                            <div class="message-data" style="text-align: left; margin-bottom: 5px;">
                                <span class="message-data-time">' . $row['msg_time'] . '</span>
                            </div>
                            <div class="message other-message" style="float: left; clear: both;">' . $row['msg_contant'] . '</div>
                        </li>';
        }
    }

    echo json_encode($message);
}


// <---------Clear chat history ------------->
if (isset($_POST['action']) && $_POST['action'] == 'clear_chat_history') {
    $userId = $_SESSION['srno'];
    $friendId = $_POST['friendId'];

    // Perform a delete query to clear the chat history between the user and the friend
    $deleteQuery = "DELETE FROM `message` WHERE (sender_id = '$userId' AND receiver_id = '$friendId') OR (sender_id = '$friendId' AND receiver_id = '$userId')";
    if (mysqli_query($conn, $deleteQuery)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error clearing chat history']);
    }
}


// <---------Get friend list and chat groups in messanger.php ------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_friend_list_and_groups') {
    $loggedInUserId = $_SESSION['srno'];
    $user_id = $_SESSION['srno']; // For generic use below if needed
    $response = array(
        'friendCard1' => array(),
        'friendCard2' => array(),
        'chatGroups' => array()
    );

    // Fetch friend list filtered by blocks
    $friendRequestsQuery = "SELECT * FROM `friend_request` WHERE ((sender_id = '$loggedInUserId' AND request_status = 'friend') OR (receiver_id = '$loggedInUserId' AND request_status = 'friend')) 
                             AND (CASE 
                                 WHEN sender_id = '$loggedInUserId' THEN receiver_id NOT IN (SELECT blocked_user_id FROM user_blocks WHERE blocker_id = '$loggedInUserId') AND receiver_id NOT IN (SELECT blocker_id FROM user_blocks WHERE blocked_user_id = '$loggedInUserId')
                                 ELSE sender_id NOT IN (SELECT blocked_user_id FROM user_blocks WHERE blocker_id = '$loggedInUserId') AND sender_id NOT IN (SELECT blocker_id FROM user_blocks WHERE blocked_user_id = '$loggedInUserId')
                             END)";
    $friendRequestsResult = mysqli_query($conn, $friendRequestsQuery);
    while ($row = mysqli_fetch_assoc($friendRequestsResult)) {
        if ($row['sender_id'] == $loggedInUserId) {
            $friendId = $row['receiver_id'];
        } else {
            $friendId = $row['sender_id'];
        }

        // Retrieve friend details from the user_data table
        $friendsQuery = "SELECT * FROM `user_data` WHERE srno = $friendId";
        $friendsResult = mysqli_query($conn, $friendsQuery);
        $friendRow = mysqli_fetch_assoc($friendsResult);

        // Fetch the last message time
        $lastMessageQuery = "SELECT MAX(msg_time) AS last_message_time FROM message WHERE (sender_id = '$loggedInUserId' AND receiver_id = '$friendId') OR (sender_id = '$friendId' AND receiver_id = '$loggedInUserId')";
        $lastMessageResult = mysqli_query($conn, $lastMessageQuery);
        $lastMessageRow = mysqli_fetch_assoc($lastMessageResult);
        $lastMessageTime = $lastMessageRow['last_message_time'];

        $unreadQuery = "SELECT COUNT(*) AS unread_count FROM message WHERE sender_id = '$friendId' AND receiver_id = '$loggedInUserId' AND is_read = 0";
        $unreadResult = mysqli_query($conn, $unreadQuery);
        $unreadRow = mysqli_fetch_assoc($unreadResult);
        $unreadCount = $unreadRow['unread_count'];

        // Display the friend's details
        $friendCard = '<li class="clearfix friend_card" data-friend-id ="' . $friendRow['srno'] . '">'
            . '<img src="post_img/' . $friendRow['user_image'] . '" alt="avatar" />'
            . '<div class="about">'
            . '<div class="name">' . $friendRow['user_firstname'] . ' ' . $friendRow['user_surname'] . '</div>'
            . '</div>'
            . '<div class="nu_unread_msg">' . ($unreadCount > 0 ? $unreadCount : '') . '</div>'
            . '<span class="last-message-time">' . $lastMessageTime . '</span>'
            . '</li>';

        // Store the friend card HTML in the appropriate array based on the request status
        if ($row['sender_id'] == $loggedInUserId) {
            $response['friendCard1'][] = $friendCard;
        } else {
            $response['friendCard2'][] = $friendCard;
        }
    }

    // Fetch chat groups and their members
    // $chatGroupsQuery = "SELECT * FROM `chat_groups`";
    // $chatGroupsResult = mysqli_query($conn, $chatGroupsQuery);
    // while ($groupRow = mysqli_fetch_assoc($chatGroupsResult)) {
    //     $groupId = $groupRow['group_id'];
    //     $groupImage = $groupRow['group_image'];
    //     $groupName = $groupRow['group_name'];

    //     // Fetch group members count
    //     $groupMembersQuery = "SELECT COUNT(*) AS member_count FROM `chat_group_member` WHERE `group_id` = '$groupId'";
    //     $groupMembersResult = mysqli_query($conn, $groupMembersQuery);
    //     $groupMembersRow = mysqli_fetch_assoc($groupMembersResult);
    //     $memberCount = $groupMembersRow['member_count'];

    //     $response['chatGroups'][] = array(
    //         'group_id' => $groupId,
    //         'group_name' => $groupName,
    //         'group_image' => $groupImage,
    //         'member_count' => $memberCount
    //     );
    // }

    echo json_encode($response);
    exit;
}


// <---------Check uploaded story has image------------->
function isImage($fileType)
{
    $imageTypes = array('image/jpeg', 'image/png', 'image/gif');
    return in_array($fileType, $imageTypes);
}


// <---------Check uploaded story has video------------->
function isVideo($fileType)
{
    $videoTypes = array('video/mp4', 'video/mpeg', 'video/quicktime');
    return in_array($fileType, $videoTypes);
}


// <---------Add Story to database------------->
if (isset($_POST['action']) && $_POST['action'] == 'add_story_database') {
    // Get form data
    $user_create_by = $_SESSION['srno'];
    $story_caption = htmlspecialchars($_POST['insert_story_caption']);
    $story_media = $_FILES['insert_story_media'];
    if ($story_media['error'] === 0) {
        $fileType = $story_media['type'];
        $filePath = 'story_media/' . basename($story_media['name']);

        // Check if the uploaded file is an image or video
        if (isImage($fileType) || isVideo($fileType)) {
            if (isVideo($fileType)) {
                $videoDuration = $_POST['video_duration'];
                if ($videoDuration < 1 || $videoDuration > 30) {
                    echo "Video length must be between 1 and 30 seconds. " . $videoDuration;
                    exit;
                }
            }

            if (move_uploaded_file($story_media['tmp_name'], $filePath)) {
                // Insert the story into the database
                $story_type = isImage($fileType) ? 'image' : 'video';
                // echo $user_create_by . ' ' . $story_caption . ' ' . $filePath . ' ' . $story_type;
                // Use prepared statement to insert data
                $stmt = "INSERT INTO `story`(`user_create_by`, `story_caption`, `story_media`, `story_type`) VALUES ($user_create_by,'$story_caption','$filePath',' $story_type')";
                $result = mysqli_query($conn, $stmt);
                echo "Story added successfully!";
            } else {
                echo "Error uploading the file.";
            }
        } else {
            echo "Invalid file type. Only images and videos are allowed.";
        }
    } else {
        echo "Error uploading the file.";
    }
}


// <---------Delete old Story to database------------->
if (isset($_POST['action']) && $_POST['action'] == 'delete_old_story') {
    date_default_timezone_set('Asia/Kolkata');
    $twentyFourHoursAgo = date("Y-m-d H:i:s", strtotime('-24 hours'));

    $stmt = "DELETE FROM story WHERE created_at < '$twentyFourHoursAgo'";
    $result = mysqli_query($conn, $stmt);
    // echo "$twentyFourHoursAgo";
    echo "Old stories deleted successfully!";
}


// <---------Delete specific Story ------------->
if (isset($_POST['action']) && $_POST['action'] == 'delete_story') {
    $user_id = $_SESSION['srno'];
    $story_id = $_POST['story_id'];

    $stmt = "DELETE FROM story WHERE story_id = '$story_id' AND user_create_by = '$user_id'";
    $result = mysqli_query($conn, $stmt);

    if (mysqli_affected_rows($conn) > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error deleting story']);
    }
    exit;
}


// <---------Get stories to database------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_stories') {
    $user_id = $_SESSION['srno'];
    // Query to fetch the latest stories
    $stmt = "SELECT * FROM story ORDER BY created_at DESC";
    $result = mysqli_query($conn, $stmt);

    $stories = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $user_srno = $row['user_create_by'];
        $userStmt = "SELECT * FROM `user_data` WHERE srno = $user_srno";
        $userResult = mysqli_query($conn, $userStmt);
        $userRow = mysqli_fetch_assoc($userResult);

        if (!$userRow)
            continue;

        // Skip if blocked
        $blo_ch_q = "SELECT 1 FROM user_blocks WHERE (blocker_id = '$user_srno' AND blocked_user_id = '$user_id') OR (blocker_id = '$user_id' AND blocked_user_id = '$user_srno')";
        $blo_ch_res = mysqli_query($conn, $blo_ch_q);
        if (mysqli_num_rows($blo_ch_res) > 0)
            continue;

        $created_at = strtotime($row['created_at']);
        $now = time();
        $diff = max(0, $now - $created_at);
        $mins = floor($diff / 60);
        $time_ago = $mins < 60 ? $mins . 'm' : floor($diff / 3600) . 'h';

        $story = array(
            'story_id' => $row['story_id'],
            'user_image' => $userRow['user_image'],
            'story_caption' => $row['story_caption'],
            'story_media' => $row['story_media'],
            'created_at' => $row['created_at'],
            'time_ago' => $time_ago,
            'story_type' => $row['story_type'],
            'user_firstname' => $userRow['user_firstname'],
            'user_surname' => $userRow['user_surname'],
            'user_create_by' => $row['user_create_by'],
        );

        $stories[] = $story;
    }

    // Return the stories data in JSON format
    header('Content-Type: application/json');
    echo json_encode($stories);
}

// <---------View stories to database------------->
if (isset($_POST['action']) && $_POST['action'] == 'view_stories') {
    $user_id = $_SESSION['srno'];
    // Query to fetch all latest stories for navigation
    $stmt = "SELECT * FROM story ORDER BY story_id DESC";
    $result = mysqli_query($conn, $stmt);

    $stories = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $user_srno = $row['user_create_by'];
        $userStmt = "SELECT * FROM `user_data` WHERE srno = $user_srno";
        $userResult = mysqli_query($conn, $userStmt);
        $userRow = mysqli_fetch_assoc($userResult);

        if (!$userRow)
            continue;

        // Skip if blocked
        $blo_ch_q = "SELECT 1 FROM user_blocks WHERE (blocker_id = '$user_srno' AND blocked_user_id = '$user_id') OR (blocker_id = '$user_id' AND blocked_user_id = '$user_srno')";
        $blo_ch_res = mysqli_query($conn, $blo_ch_q);
        if (mysqli_num_rows($blo_ch_res) > 0)
            continue;

        $created_at = strtotime($row['created_at']);
        $now = time();
        $diff = max(0, $now - $created_at);
        $mins = floor($diff / 60);
        $time_ago = $mins < 60 ? $mins . 'm' : floor($diff / 3600) . 'h';

        $story = array(
            'story_id' => $row['story_id'],
            'user_image' => $userRow['user_image'],
            'story_caption' => $row['story_caption'],
            'story_media' => $row['story_media'],
            'created_at' => $row['created_at'],
            'time_ago' => $time_ago,
            'story_type' => $row['story_type'],
            'user_firstname' => $userRow['user_firstname'],
            'user_surname' => $userRow['user_surname'],
            'user_create_by' => $row['user_create_by'],
        );

        $stories[] = $story;
    }

    // Return the stories data in JSON format
    header('Content-Type: application/json');
    echo json_encode($stories);
}

// <---------Fetch notifications for the logged-in user------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_notifications') {
    $loggedInUserSrno = $_SESSION['srno'];
    $notificationsQuery = "SELECT n.notification_id, n.user_srno, n.message, n.timestamp, n.is_read, u.user_firstname, u.user_surname FROM notifications n INNER JOIN user_data u ON n.user_srno = u.srno WHERE n.response_who_show = $loggedInUserSrno ORDER BY n.timestamp DESC";

    $notificationsResult = mysqli_query($conn, $notificationsQuery);

    $notifications = array();
    while ($row = mysqli_fetch_assoc($notificationsResult)) {
        $notification = array(
            'notification_id' => $row['notification_id'],
            'sender_name' => $row['user_firstname'] . ' ' . $row['user_surname'],
            'message' => $row['message'],
            'timestamp' => $row['timestamp'],
            'is_read' => $row['is_read']
        );
        $notifications[] = $notification;
    }

    $response = array('notifications' => $notifications);
    echo json_encode($response);
    exit;
}


// <---------Fetch notifications count for the logged-in user AND DELETE old read notification after 1hour------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_unread_notification_count') {
    $loggedInUserSrno = $_SESSION['srno'];
    // Query to delete notifications older than 1 hour
    $deleteOldNotificationsQuery = "DELETE FROM notifications WHERE is_read = 1 AND timestamp < (NOW() - INTERVAL 1 HOUR)";
    $deleteOldNotificationsResult = mysqli_query($conn, $deleteOldNotificationsQuery);

    // Query to fetch the count of unread notifications
    $unreadNotificationQuery = "SELECT COUNT(*) AS unread_count FROM notifications WHERE response_who_show = '$loggedInUserSrno' AND is_read = '0'";
    $unreadNotificationResult = mysqli_query($conn, $unreadNotificationQuery);
    $unreadNotificationRow = mysqli_fetch_assoc($unreadNotificationResult);
    $unreadNotificationCount = $unreadNotificationRow['unread_count'];

    echo $unreadNotificationCount;
    exit;
}


// <---------Mark Notification read ------------->
if (isset($_POST['action']) && $_POST['action'] == 'mark_notifications_as_read') {
    $loggedInUserSrno = $_SESSION['srno'];

    // Query to update notifications as read and update timestamp
    $updateNotificationsQuery = "UPDATE notifications SET is_read = 1, `timestamp` = NOW() WHERE response_who_show = $loggedInUserSrno";
    $updateNotificationsResult = mysqli_query($conn, $updateNotificationsQuery);

    if ($updateNotificationsResult) {
        $response = array('success' => true, 'message' => 'Notifications marked as read.');
    } else {
        $response = array('success' => false, 'message' => 'Failed to mark notifications as read.');
    }

    echo json_encode($response);
    exit;
}

// <---------Like Comment Functionality------------->
if (isset($_POST['action']) && $_POST['action'] == 'like_comment') {
    $postId = $_POST['post_id'];
    $commentId = $_POST['comment_id'];
    $user_srno = $_SESSION['srno'];

    $checkQuery = "SELECT * FROM `comment_likes` WHERE `comment_id` = '$commentId' AND `user_srno` = '$user_srno'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) === 0) {
        $insertQuery = "INSERT INTO `comment_likes` (`comment_id`, `user_srno`) VALUES ('$commentId', '$user_srno')";
        if (mysqli_query($conn, $insertQuery)) {
            $countQuery = "SELECT COUNT(*) as count FROM `comment_likes` WHERE `comment_id` = '$commentId'";
            $countResult = mysqli_query($conn, $countQuery);
            $countRow = mysqli_fetch_assoc($countResult);
            echo json_encode(['success' => true, 'action' => 'liked', 'likeCount' => $countRow['count']]);
        }
    } else {
        $deleteQuery = "DELETE FROM `comment_likes` WHERE `comment_id` = '$commentId' AND `user_srno` = '$user_srno'";
        if (mysqli_query($conn, $deleteQuery)) {
            $countQuery = "SELECT COUNT(*) as count FROM `comment_likes` WHERE `comment_id` = '$commentId'";
            $countResult = mysqli_query($conn, $countQuery);
            $countRow = mysqli_fetch_assoc($countResult);
            echo json_encode(['success' => true, 'action' => 'unliked', 'likeCount' => $countRow['count']]);
        }
    }
    exit;
}
// <---------Update Comment Functionality------------->
if (isset($_POST['action']) && $_POST['action'] == 'update_comment') {
    $commentId = $_POST['comment_id'];
    $commentText = mysqli_real_escape_string($conn, $_POST['comment_text']);
    $user_srno = $_SESSION['srno'];

    $updateQuery = "UPDATE `post_comment` SET `comment_text` = '$commentText' WHERE `comment_id` = '$commentId' AND `comment_user_srno` = '$user_srno'";

    if (mysqli_query($conn, $updateQuery)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
    exit;
}

// <---------Get Profile Stats Functionality------------->
if (isset($_POST['action']) && $_POST['action'] == 'get_profile_stats') {
    $user_srno = $_SESSION['srno'];

    // Count Friends
    $friends_q = "SELECT COUNT(*) as friend_count FROM `friend_request` WHERE (`sender_id` = '$user_srno' OR `receiver_id` = '$user_srno') AND `request_status` = 'friend'";
    $friends_res = mysqli_query($conn, $friends_q);
    $friends_row = mysqli_fetch_assoc($friends_res);
    $friends_count = $friends_row['friend_count'];

    // Count Posts
    $posts_q = "SELECT COUNT(*) as post_count FROM `post` WHERE `post_user_srno` = '$user_srno'";
    $posts_res = mysqli_query($conn, $posts_q);
    $posts_row = mysqli_fetch_assoc($posts_res);
    $posts_count = $posts_row['post_count'];

    echo json_encode([
        'success' => true,
        'friends_count' => $friends_count,
        'posts_count' => $posts_count
    ]);
    exit;
}
?>