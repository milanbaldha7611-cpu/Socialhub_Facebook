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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body class="light-mode">
    <?php include "navbar.php"; ?>
    <?php

        if( isset( $_POST['vcu_post_update_save'] ) && $_POST['vcu_post_update_save'] == 'Save Changes' )
        {
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
            
            header('Location: welcome.php');
            exit();
        }


    ?>
    <div class="section">
        <div class="story_area">

            <div class="creat_story">
                <div id="add_story" class="rtl">
                    <div id="add">
                        <img src="icon/add.png" alt="">
                    </div>
                    <div id="addStory">
                        <img src="icon/instagram-stories.png" alt="">
                        <p>Add story</p>
                    </div>
                    <div id="addPost">
                        <img src="icon/picture.png" alt="">
                        <p>Add post</p>
                    </div>
                </div>
                <p>Post/Story</p>
            </div>

            <div class="story">
            </div>
        </div>
    </div>

    <!-- The modal for view story -->
    <div class="modal" id="view_stories_modal">
        <div class="section">
            <div class="show_story_area">
                <div class="story__container">

                </div>
            </div>
        </div>
    </div>


    <div class="section">
        <div class="add_story">
            <h3>Add story</h3>
            <form id="addStoryForm" method="Post" enctype="multipart/form-data">
                <div class="story_caption">
                    <label for="insert_story_caption">Add Caption :</label><br>
                    <input id="insert_story_caption" name="insert_story_caption" type="text"
                        placeholder="Write caption..">
                </div>
                <div class="story_media">
                    <label for="insert_story_media">Add media (Image or Video) :</label><br>
                    <input id="insert_story_media" name="insert_story_media" type="file" class="preview-input" data-preview="#story_preview">
                    <!-- Preview Container -->
                    <div id="story_preview" class="preview-box"></div>
                </div>
                <!-- <input type="button" value="Add story" name="add_story_btn" id="add_story_btn"> -->
                <button type="button" id="add_story_btn">Add Story</button>
            </form>
        </div>
        <div class="add_post">
            <h3>Add new post</h3>
            <form id="addPostForm" enctype="multipart/form-data">
                <div class="post_caption">
                    <label for="insert_caption">Add Caption :</label><br>
                    <textarea id="insert_caption" name="caption" placeholder="What's on your mind?"></textarea>
                </div>
                <div class="post_media">
                    <label for="insert_media">Add media (Image or Video) :</label><br>
                    <input id="insert_media" name="media" type="file" class="preview-input" data-preview="#post_preview">
                    <!-- Preview Container -->
                    <div id="post_preview" class="preview-box"></div>
                </div>
                <input type="button" value="Add post" name="add_post_btn" id="add_post_btn">
            </form>
        </div>
    </div>

    <!--------Post Print Area-------->
    <div class="section" id="postsContainer">

    </div>


    <!-- Edit Post Popup -->
    <div class="vcu_popup_wrapper">
        <div class="vcu_popup_content">
            <div class="vcu_popup_heading">
               <h2>Edit Post</h2> 
               <i class="fas fa-times vcu_popup_close"></i>
            </div>
            <div class="vcu_popup_form">
                <form action="welcome.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="vcu_update_post_id" id="vcu_update_post_id">
                    <input type="hidden" name="vcu_remove_media" id="vcu_remove_media" value="0">
                    
                    <div class="vcu_popup_field">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label style="margin-bottom: 0;">Current Media Preview</label>
                            <button type="button" id="vcu_remove_media_btn" class="btn btn-sm btn-outline-danger" style="display: none; padding: 2px 8px; font-size: 12px; border-radius: 6px; border: 1px solid #ef4444; color: #ef4444; background: transparent; cursor: pointer; transition: 0.3s; font-weight: 500;">
                                <i class="fas fa-trash-alt me-1"></i> Remove Media
                            </button>
                        </div>
                        <div class="vcu_edit_preview" id="vcu_edit_preview_box">
                            <!-- Preview injected by JS -->
                        </div>
                    </div>

                    <div class="vcu_popup_field">
                        <label><i class="fas fa-camera-retro me-1"></i> Change Photo or Video</label>
                        <input type="file" name="vcu_post_media" id="vcu_post_media_input" class="form-control" accept="image/*,video/*">
                    </div>

                    <div class="vcu_popup_field">
                        <label><i class="fas fa-quote-left me-1"></i> Post Caption</label>
                        <textarea placeholder="Tell your friends about this..." name="vcu_post_caption" id="vcu_post_caption" rows="3"></textarea>
                    </div>

                    <button type="submit" name="vcu_post_update_save" value="Save Changes" id="vcu_post_update_save">
                        <i class="fas fa-check-circle me-1"></i> Update Post
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>