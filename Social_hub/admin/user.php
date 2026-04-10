<?php

session_start();

if (isset($_SESSION['is_loggedin']) && $_SESSION['is_loggedin']) 
{

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    include '../_db_connect.php';

    if (isset($_GET['delete'])) {
        $user_id = $_GET['delete'];

        $sql = "DELETE FROM `message` WHERE sender_id='$user_id' OR receiver_id='$user_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `notifications` WHERE user_srno='$user_id' OR response_who_show='$user_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `story` WHERE user_create_by='$user_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `friend_request` WHERE sender_id='$user_id' OR receiver_id='$user_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `post` WHERE  post_user_srno='$user_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `post_comment` WHERE  comment_user_srno='$user_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `post_likes` WHERE  user_srno='$user_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `user_data` WHERE  srno='$user_id'";
        mysqli_query($conn, $sql);

        header('location:user.php?delete_success=1');
        exit;

    }
    if (isset($_GET['delete_post'])) {
        $post_id = $_GET['delete_post'];

        $sql = "DELETE FROM `post_comment` WHERE  post_id='$post_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `post_likes` WHERE  post_id='$post_id'";
        mysqli_query($conn, $sql);

        $sql = "DELETE FROM `post` WHERE  post_id='$post_id'";
        mysqli_query($conn, $sql);

        header('location:user.php?delete_post_success=1');
        exit;
    }

    if (isset($_GET['delete_story'])) {
        $story_id = $_GET['delete_story'];
        $sql = "DELETE FROM `story` WHERE  story_id='$story_id'";
        mysqli_query($conn, $sql);
        header('location:user.php?delete_story_success=1');
        exit;
    }

    if (isset($_GET['delete_msg'])) {
        $msg_id = $_GET['delete_msg'];
        $sql = "DELETE FROM `message` WHERE msg_id='$msg_id'";
        mysqli_query($conn, $sql);
        header('location:user.php?delete_msg_success=1');
        exit;
    }

    if (isset($_GET['toggle_block'])) {
        $srno = $_GET['toggle_block'];
        $sql = "UPDATE `user_data` SET is_blocked = NOT is_blocked WHERE srno='$srno'";
        mysqli_query($conn, $sql);
        header('location:user.php?block_success=1');
        exit;
    }

    if (isset($_POST['update_user'])) {
        $srno = $_POST['user_srno'];
        $fname = mysqli_real_escape_string($conn, $_POST['user_firstname']);
        $sname = mysqli_real_escape_string($conn, $_POST['user_surname']);
        $email = mysqli_real_escape_string($conn, $_POST['user_id']);
        $dob = $_POST['user_dob'];
        $gender = $_POST['user_gender'];

        $update_img_sql = "";
        if (!empty($_FILES['user_image']['name'])) {
            $img_name = time() . '_' . $_FILES['user_image']['name'];
            if (move_uploaded_file($_FILES['user_image']['tmp_name'], '../post_img/' . $img_name)) {
                $update_img_sql = ", user_image='$img_name'";
            }
        }

        $sql = "UPDATE `user_data` SET user_firstname='$fname', user_surname='$sname', user_id='$email', user_dob='$dob', user_gender='$gender' $update_img_sql WHERE srno='$srno'";
        mysqli_query($conn, $sql);
        header('location:user.php?update_success=1');
        exit;
    }

    if (isset($_POST['update_post']) || isset($_POST['update_post_caption']) || isset($_POST['update_post_media'])) {
        $post_id = $_POST['post_id'];

        if (isset($_POST['update_post_caption'])) {
            $caption = mysqli_real_escape_string($conn, $_POST['post_caption']);
            $sql = "UPDATE `post` SET post_caption='$caption' WHERE post_id='$post_id'";
        }
        elseif (isset($_POST['update_post_media'])) {
            if (!empty($_FILES['post_image']['name'])) {
                $img_name = time() . '_' . $_FILES['post_image']['name'];
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], '../post_img/' . $img_name)) {
                    $sql = "UPDATE `post` SET post_image='$img_name' WHERE post_id='$post_id'";
                }
            }
        }
        else {
            $caption = mysqli_real_escape_string($conn, $_POST['post_caption']);
            $update_img_sql = "";
            if (!empty($_FILES['post_image']['name'])) {
                $img_name = time() . '_' . $_FILES['post_image']['name'];
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], '../post_img/' . $img_name)) {
                    $update_img_sql = ", post_image='$img_name'";
                }
            }
            $sql = "UPDATE `post` SET post_caption='$caption' $update_img_sql WHERE post_id='$post_id'";
        }

        mysqli_query($conn, $sql);
        header('location:user.php?update_success=1');
        exit;
    }

    if (isset($_POST['update_msg'])) {
        $msg_id = $_POST['msg_id'];
        $content = mysqli_real_escape_string($conn, $_POST['msg_content']);

        $sql = "UPDATE `message` SET msg_contant='$content' WHERE msg_id='$msg_id'";
        mysqli_query($conn, $sql);
        header('location:user.php?update_success=1');
        exit;
    }

    if (isset($_POST['update_story']) || isset($_POST['update_story_media'])) {
        $story_id = $_POST['story_id'];
        
        if (isset($_POST['update_story_media'])) {
            if (!empty($_FILES['story_media']['name'])) {
                $img_name = time() . '_' . $_FILES['story_media']['name'];
                $target_path = '../story_media/' . $img_name;
                $db_path = 'story_media/' . $img_name;
                if (move_uploaded_file($_FILES['story_media']['tmp_name'], $target_path)) {
                    $fileType = $_FILES['story_media']['type'];
                    $story_type = strpos($fileType, 'video') !== false ? 'video' : 'image';
                    $sql = "UPDATE `story` SET story_media='$db_path', story_type='$story_type' WHERE story_id='$story_id'";
                }
            }
        } else {
            $caption = mysqli_real_escape_string($conn, $_POST['story_caption']);
            $sql = "UPDATE `story` SET story_caption='$caption' WHERE story_id='$story_id'";
        }
        
        if (isset($sql)) mysqli_query($conn, $sql);
        header('location:user.php?update_success=1');
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM `user_data` WHERE `srno`='$user_id'";

    $result = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($result);

    $name = $_SESSION['username'];

?>
        <!doctype html>
        <html lang="en">

        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <title>Admin Dashboard | Social Hub</title>
            <link rel="icon" type="image/png" href="../img/social_hub_logo.png">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
            <link rel="stylesheet" href="../style.css">

            <style>
                body {
                    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
                    background-attachment: fixed !important;
                    color: #f8fafc !important;
                    font-family: 'Inter', sans-serif !important;
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                }

                .admin-header {
                    background: rgba(15, 23, 42, 0.85);
                    backdrop-filter: blur(15px);
                    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
                    position: sticky;
                    top: 0;
                    z-index: 1040;
                    padding: 15px 40px;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                }

                .admin-nav-content {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .admin-logo img {
                    height: 50px;
                    border-radius: 50%;
                    box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
                }

                .admin-greeting {
                    font-size: 1.2rem;
                    font-weight: 700;
                    color: #f8fafc;
                    margin-right: 20px;
                }

                .btn-logout {
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: white;
                    border: none;
                    padding: 8px 24px;
                    border-radius: 12px;
                    font-weight: 600;
                    transition: transform 0.2s, box-shadow 0.2s;
                }

                .btn-logout:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4);
                    color: white;
                }

                .admin-container {
                    background: rgba(255, 255, 255, 0.03);
                    border: 1px solid rgba(255, 255, 255, 0.05);
                    border-radius: 20px;
                    padding: 30px;
                    margin-top: 50px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                }

                .admin-title {
                    font-weight: 800;
                    color: #3b82f6;
                    margin-bottom: 20px;
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }

                .custom-table {
                    color: #f8fafc;
                }

                .custom-table thead th {
                    background: rgba(15, 23, 42, 0.6);
                    color: #cbd5e1;
                    border-bottom: 2px solid rgba(59, 130, 246, 0.3);
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    font-size: 0.85rem;
                }

                .custom-table tbody tr {
                    transition: all 0.2s ease;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                }

                .custom-table tbody tr:hover {
                    background: rgba(255, 255, 255, 0.05);
                }

                .custom-table td {
                    vertical-align: middle;
                    color: #e2e8f0;
                }

                .btn-delete {
                    background: rgba(239, 68, 68, 0.1);
                    color: #ef4444;
                    border: 1px solid rgba(239, 68, 68, 0.3);
                    border-radius: 8px;
                    width: 38px;
                    height: 38px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 0.85rem;
                    transition: all 0.2s;
                    margin-left: 6px;
                }

                .btn-delete:hover {
                    background: #ef4444;
                    color: white;
                    transform: scale(1.05);
                }

                .action-btns {
                    text-align: right;
                    vertical-align: middle !important;
                    white-space: nowrap;
                    padding: 10px 15px !important;
                }
                
                .text-end-header {
                    text-align: right !important;
                }

                /* DataTables Custom Styling */
                .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {
                    color: #94a3b8;
                }
                .dataTables_wrapper .dataTables_filter input, .dataTables_length select {
                    background: rgba(15, 23, 42, 0.6);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    color: white;
                    border-radius: 6px;
                    padding: 4px 10px;
                    outline: none;
                }
                .page-item.disabled .page-link {
                    background: rgba(15, 23, 42, 0.4);
                    border-color: rgba(255, 255, 255, 0.05);
                    color: #64748b;
                }
                .page-item .page-link {
                    background: rgba(15, 23, 42, 0.8);
                    border-color: rgba(255, 255, 255, 0.1);
                    color: #e2e8f0;
                }
                .btn-edit {
                    background: rgba(59, 130, 246, 0.1);
                    color: #3b82f6;
                    border: 1px solid rgba(59, 130, 246, 0.3);
                    border-radius: 8px;
                    width: 38px;
                    height: 38px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 0.85rem;
                    transition: all 0.2s;
                    margin-left: 6px;
                }

                .btn-edit:hover {
                    background: #3b82f6;
                    color: white;
                    transform: scale(1.05);
                }

                .image-preview-wrapper {
                    width: 100%;
                    height: 180px;
                    border: 2px dashed rgba(255, 255, 255, 0.1);
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                    margin-bottom: 15px;
                    background: rgba(0, 0, 0, 0.2);
                }
                .image-preview-wrapper img {
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: contain;
                }

                .modal-content {
                    background: #1e293b;
                    color: #f8fafc;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                }
                .modal-header {
                    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                }
                .modal-footer {
                    border-top: 1px solid rgba(255, 255, 255, 0.05);
                }
                .filter-btn {
                    padding: 8px 20px;
                    border-radius: 50px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    background: rgba(255, 255, 255, 0.05);
                    color: #94a3b8;
                    font-size: 0.85rem;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .filter-btn:hover {
                    background: rgba(255, 255, 255, 0.1);
                    color: #e2e8f0;
                }

                .filter-btn.active {
                    background: linear-gradient(135deg, #3b82f6, #6366f1);
                    color: white;
                    border-color: transparent;
                    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
                }

                .form-control {
                    background: rgba(15, 23, 42, 0.6);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    color: white !important;
                }
                .form-control:focus {
                    background: rgba(15, 23, 42, 0.8);
                    border-color: #3b82f6;
                    box-shadow: 0 0 10px rgba(59, 130, 246, 0.2);
                }

                .page-item.active .page-link {
                    background: #3b82f6;
                    border-color: #3b82f6;
                }

                /* Fixed Modal Issues - Ensure modals are above everything */
                .modal {
                    z-index: 2000 !important;
                }
                .modal-backdrop {
                    z-index: 1050 !important;
                    background-color: rgba(0, 0, 0, 0.7);
                }
                .modal-content {
                    background: #1e293b;
                    color: #f8fafc;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
                }

            </style>
        </head>

        <body>
            <header class="admin-header">
                <div class="container-fluid admin-nav-content">
                    <div class="admin-logo">
                        <a href="../welcome.php"><img src="../img/social_hub_logo.png" alt="Logo"></a>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="admin-greeting"><i class="fas fa-crown text-warning me-2"></i>Welcome, <?php echo htmlspecialchars($name); ?></span>
                        <a href="logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </div>
                </div>
            </header>

            <div class="container-fluid px-md-5 mt-5" style="flex: 1;">
                <!-- Stats Row -->
                <?php
    $total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user_data WHERE user_role!='admin'"));
    $total_posts = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM post"));
    $total_msgs = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM message"));
?>
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="admin-container mt-0 p-4 text-center">
                            <i class="fas fa-users-cog mb-3 text-primary" style="font-size: 2.5rem;"></i>
                            <h3 class="fw-bold"><?php echo $total_users; ?></h3>
                            <p class="text-muted mb-0">Total Active Users</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-container mt-0 p-4 text-center">
                            <i class="fas fa-photo-video mb-3 text-success" style="font-size: 2.5rem;"></i>
                            <h3 class="fw-bold"><?php echo $total_posts; ?></h3>
                            <p class="text-muted mb-0">Total Posts Shared</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-container mt-0 p-4 text-center">
                            <i class="fas fa-comments mb-3 text-info" style="font-size: 2.5rem;"></i>
                            <h3 class="fw-bold"><?php echo $total_msgs; ?></h3>
                            <p class="text-muted mb-0">Total Messages Sent</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <ul class="nav nav-pills justify-content-center mb-5" id="adminTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="pill" data-bs-target="#users-section" type="button" role="tab"><i class="fas fa-users me-2"></i>Users</button>
                    </li>
                    <li class="nav-item mx-3" role="presentation">
                        <button class="nav-link" id="posts-tab" data-bs-toggle="pill" data-bs-target="#posts-section" type="button" role="tab"><i class="fas fa-images me-2"></i>Posts</button>
                    </li>
                    <li class="nav-item me-3" role="presentation">
                        <button class="nav-link" id="stories-tab" data-bs-toggle="pill" data-bs-target="#stories-section" type="button" role="tab"><i class="fas fa-history me-2"></i>Stories</button>
                    </li>
                    <li class="nav-item me-3" role="presentation">
                        <button class="nav-link" id="chats-tab" data-bs-toggle="pill" data-bs-target="#chats-section" type="button" role="tab"><i class="fas fa-comments me-2"></i>Chats</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="blocked-tab" data-bs-toggle="pill" data-bs-target="#blocked-section" type="button" role="tab"><i class="fas fa-user-slash me-2"></i>Blocked Status</button>
                    </li>
                </ul>

                <?php
    if (isset($_GET['delete_success']) || isset($_GET['delete_post_success']) || isset($_GET['delete_story_success']) || isset($_GET['delete_msg_success']) || isset($_GET['update_success']) || isset($_GET['block_success'])) {
        $msg = "Action completed successfully.";
        if (isset($_GET['delete_success']))
            $msg = "User has been permanently deleted.";
        if (isset($_GET['delete_post_success']))
            $msg = "Post has been permanently deleted.";
        if (isset($_GET['delete_story_success']))
            $msg = "Story has been permanently deleted.";
        if (isset($_GET['delete_msg_success']))
            $msg = "Message has been permanently deleted.";
        if (isset($_GET['update_success']))
            $msg = "Changes have been saved successfully.";
        if (isset($_GET['block_success']))
            $msg = "User block status updated successfully.";

        $alertClass = (isset($_GET['update_success'])) ? "alert-info" : "alert-success";
        $icon = (isset($_GET['update_success'])) ? "fa-info-circle" : "fa-check-circle";
        $color = (isset($_GET['update_success'])) ? "#3b82f6" : "#10b981";
        $bgColor = (isset($_GET['update_success'])) ? "rgba(59, 130, 246, 0.1)" : "rgba(16, 185, 129, 0.1)";

        echo "<div class='alert $alertClass alert-dismissible fade show mt-2 auto-close-alert' style='background: $bgColor; color: $color; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; z-index: 1050; position: relative;' role='alert'>
                        <i class='fas $icon me-2'></i> <strong>Success!</strong> $msg
                        <button type='button' class='btn-close btn-close-white' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    }
?>

                <div class="tab-content" id="adminTabsContent">
                    <!-- Users Section -->
                    <div class="tab-pane fade show active" id="users-section" role="tabpanel">
                        <div class="admin-container mt-0">
                            <h2 class="admin-title"><i class="fas fa-users"></i> User Management</h2>
                    
                    <?php
    $sql = "SELECT * FROM `user_data` WHERE user_role!='admin'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
?>
                            <div class="table-responsive">
                                <table class="table custom-table" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>First Name</th>
                                            <th>Surname</th>
                                            <th>Email / Phone</th>
                                            <th>DOB</th>
                                            <th>Gender</th>
                                            <th style="min-width: 120px;" class="text-end-header">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
        $sno = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $sno++;
            echo "<tr>
                                            <td><strong>" . $sno . "</strong></td>
                                            <td>" . htmlspecialchars($row['user_firstname']) . "</td>
                                            <td>" . htmlspecialchars($row['user_surname']) . "</td>
                                            <td>" . htmlspecialchars($row['user_id']) . "</td>
                                            <td>" . htmlspecialchars($row['user_dob']) . "</td>
                                            <td>" . htmlspecialchars($row['user_gender']) . "</td>
                                            <td class='action-btns'>
                                                <button class='edit-user btn-edit' 
                                                    data-srno='" . $row['srno'] . "'
                                                    data-fname='" . htmlspecialchars($row['user_firstname']) . "'
                                                    data-sname='" . htmlspecialchars($row['user_surname']) . "'
                                                    data-email='" . htmlspecialchars($row['user_id']) . "'
                                                    data-dob='" . $row['user_dob'] . "'
                                                    data-gender='" . $row['user_gender'] . "'
                                                    data-image='" . $row['user_image'] . "'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='delete btn-delete' id=" . $row['srno'] . "><i class='fas fa-trash-alt'></i></button>
                                            </td>
                                        </tr>";
        }
?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
    }
    else {
        echo '<div class="alert" style="background: rgba(255,255,255,0.05); color: #94a3b8; border-radius: 12px;"><i class="fas fa-info-circle me-2"></i> No active users found on the platform.</div>';
    }
?>
                        </div>
                    </div>

                    <!-- Posts Section -->
                    <div class="tab-pane fade" id="posts-section" role="tabpanel">
                        <div class="admin-container mt-0">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="admin-title mb-0"><i class="fas fa-images"></i> Content Management</h2>
                                <div class="filter-group d-flex gap-2">
                                    <button class="filter-btn active" data-filter="all">All</button>
                                    <button class="filter-btn" data-filter="image">Photos</button>
                                    <button class="filter-btn" data-filter="video">Videos</button>
                                    <button class="filter-btn" data-filter="text">Text Only</button>
                                </div>
                            </div>
                    
                    <?php
    $sql = "SELECT * FROM `post` ORDER BY post_id DESC";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
?>
                            <div class="table-responsive">
                                <table class="table custom-table" id="postsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Creator</th>
                                            <th>Type</th>
                                            <th>Media</th>
                                            <th>Caption</th>
                                            <th style="min-width: 150px;" class="text-end-header">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
        $sno = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $sno++;
            $uid = $row['post_user_srno'];
            $query = "SELECT * FROM `user_data` WHERE srno='$uid'";
            $res = mysqli_query($conn, $query);
            $user_arr = mysqli_fetch_assoc($res);
            $user_firstname = $user_arr ? $user_arr['user_firstname'] : '';
            if (!$user_arr) continue; // Remove entries with Unknown User

            // Determine post type and preview
            $post_type = 'text';
            $media_preview = '<span class="text-muted small">No Media</span>';
            $type_badge = '<span class="badge" style="background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);">Text</span>';
            
            if (!empty($row['post_image'])) {
                if (strpos($row['media_type'], 'video') !== false || substr($row['post_image'], -4) == '.mp4') {
                    $post_type = 'video';
                    $type_badge = '<span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2);">Video</span>';
                    $media_preview = "<a target='_blank' href='../post_img/" . $row['post_image'] . "' class='d-flex align-items-center justify-content-center' style='height: 50px; width: 50px; background: rgba(59, 130, 246, 0.05); border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.2); color: #3b82f6; text-decoration: none;'>
                                        <i class='fas fa-video'></i>
                                      </a>";
                } else {
                    $post_type = 'image';
                    $type_badge = '<span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);">Photo</span>';
                    $media_preview = "<a target='_blank' href='../post_img/" . $row['post_image'] . "'><img src='../post_img/" . $row['post_image'] . "' style='height: 50px; width: 50px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); object-fit: cover;'></a>";
                }
            }

            echo "<tr class='post-row' data-type='" . $post_type . "'>
                                            <td><strong>" . $sno . "</strong></td>
                                            <td>" . htmlspecialchars($user_firstname) . "</td>
                                            <td>" . $type_badge . "</td>
                                            <td>" . $media_preview . "</td>
                                            <td style='max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>" . htmlspecialchars($row['post_caption']) . "</td>
                                            <td class='action-btns'>
                                                <button class='delete_post btn-delete' id=" . $row['post_id'] . "><i class='fas fa-trash-alt'></i></button>
                                            </td>
                                        </tr>";
        }
?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
    }
    else {
        echo '<div class="alert" style="background: rgba(255,255,255,0.05); color: #94a3b8; border-radius: 12px;"><i class="fas fa-info-circle me-2"></i> No posts have been published yet.</div>';
    }
?>
                        </div>
                    </div>

                    <!-- Stories Section -->
                    <div class="tab-pane fade" id="stories-section" role="tabpanel">
                        <div class="admin-container mt-0">
                            <h2 class="admin-title"><i class="fas fa-history"></i> Stories Management</h2>
                    
                    <?php
                        $sql = "SELECT * FROM `story` ORDER BY story_id DESC";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                    ?>
                            <div class="table-responsive">
                                <table class="table custom-table" id="storiesTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Creator</th>
                                            <th>Media</th>
                                            <th>Caption</th>
                                            <th>Expires In</th>
                                            <th style="min-width: 100px;" class="text-end-header">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sno = 0;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $uid = $row['user_create_by'];
                                            $u_res = mysqli_query($conn, "SELECT user_firstname FROM user_data WHERE srno='$uid'");
                                            $u_row = mysqli_fetch_assoc($u_res);
                                            if (!$u_row) continue; // Remove entries with Unknown User
                                            
                                            $sno++;
                                            $story_media = $row['story_media'];
                                            $story_type = trim($row['story_type']);
                                            $media_preview = "";
                                            
                                            if ($story_type == 'image') {
                                                $media_preview = "<a target='_blank' href='../$story_media'><img src='../$story_media' style='height: 50px; width: 50px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); object-fit: cover;'></a>";
                                            } else {
                                                $media_preview = "<a target='_blank' href='../$story_media' class='d-flex align-items-center justify-content-center' style='height: 50px; width: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.3); color: #3b82f6; text-decoration: none;'>
                                                    <i class='fas fa-video'></i>
                                                </a>";
                                            }

                                            // Time remaining calculation (24 hours logic)
                                            $created_at = strtotime($row['created_at']);
                                            $now = time();
                                            $expires_at = $created_at + (24 * 60 * 60);

                                            if ($now > $expires_at) {
                                                $time_left_badge = "<span class='badge bg-danger'>Expired</span>";
                                            } else {
                                                $diff = $expires_at - $now;
                                                $hours = floor($diff / 3600);
                                                $minutes = floor(($diff % 3600) / 60);
                                                $time_left_badge = "<span class='badge' style='background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); filter: drop-shadow(0px 2px 4px rgba(204, 35, 102, 0.3)); border-radius: 12px; padding: 6px 12px;'><i class='far fa-clock me-1'></i> {$hours}h {$minutes}m</span>";
                                            }

                                            echo "<tr>
                                                <td><strong>" . $sno . "</strong></td>
                                                <td>" . htmlspecialchars($u_row['user_firstname']) . "</td>
                                                <td>" . $media_preview . "</td>
                                                <td style='max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>" . htmlspecialchars($row['story_caption']) . "</td>
                                                <td>" . $time_left_badge . "</td>
                                                <td class='action-btns'>
                                                    <button class='delete_story btn-delete' id=" . $row['story_id'] . "><i class='fas fa-trash-alt'></i></button>
                                                </td>
                                            </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                        }
                        else {
                            echo '<div class="alert" style="background: rgba(255,255,255,0.05); color: #94a3b8; border-radius: 12px;"><i class="fas fa-info-circle me-2"></i> No stories active at the moment.</div>';
                        }
                    ?>
                        </div>
                    </div>

                    <!-- Chats Section -->
                    <div class="tab-pane fade" id="chats-section" role="tabpanel">
                        <div class="admin-container mt-0">
                            <h2 class="admin-title"><i class="fas fa-comments"></i> Chat Management</h2>
                            
                            <?php
    $sql = "SELECT * FROM `message` ORDER BY msg_id DESC";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
?>
                                    <div class="table-responsive">
                                        <table class="table custom-table" id="chatsTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Sender</th>
                                                    <th>Receiver</th>
                                                    <th>Message</th>
                                                    <th>Time</th>
                                                    <th style="min-width: 130px;" class="text-end-header">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
        $sno = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $sno++;

            $s_id = $row['sender_id'];
            $r_id = $row['receiver_id'];

            $s_res = mysqli_query($conn, "SELECT user_firstname FROM user_data WHERE srno='$s_id'");
            $r_res = mysqli_query($conn, "SELECT user_firstname FROM user_data WHERE srno='$r_id'");

            $s_name = ($s_row = mysqli_fetch_assoc($s_res)) ? $s_row['user_firstname'] : '';
            $r_name = ($r_row = mysqli_fetch_assoc($r_res)) ? $r_row['user_firstname'] : '';

            if (!$s_row || !$r_row) continue; // Remove entries with Unknown User

            echo "<tr>
                                                    <td><strong>" . $sno . "</strong></td>
                                                    <td>" . htmlspecialchars($s_name) . "</td>
                                                    <td>" . htmlspecialchars($r_name) . "</td>
                                                    <td style='max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>" . htmlspecialchars($row['msg_contant']) . "</td>
                                                    <td>" . date('M d, H:i', strtotime($row['msg_time'])) . "</td>
                                                    <td class='action-btns'>
                                                        <button class='delete_msg btn-delete' id=" . $row['msg_id'] . "><i class='fas fa-trash-alt'></i></button>
                                                    </td>
                                                </tr>";
        }
?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
    }
    else {
        echo '<div class="alert" style="background: rgba(255,255,255,0.05); color: #94a3b8; border-radius: 12px;"><i class="fas fa-info-circle me-2"></i> No messages found in the database.</div>';
    }
?>
                        </div>
                    </div>

                    <!-- Blocked Users Section -->
                    <div class="tab-pane fade" id="blocked-section" role="tabpanel">
                        <div class="admin-container mt-0">
                            <h2 class="admin-title"><i class="fas fa-user-slash"></i> User Block Status</h2>
                            
                            <?php
                            $sql_blocked = "SELECT * FROM `user_data` WHERE user_role!='admin' ORDER BY is_blocked DESC";
                            $result_blocked = mysqli_query($conn, $sql_blocked);

                            if (mysqli_num_rows($result_blocked) > 0) {
                                ?>
                                <div class="table-responsive">
                                    <table class="table custom-table" id="blockedTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email / ID</th>
                                                <th>Status</th>
                                                <th style="min-width: 150px;" class="text-end-header">Block Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $b_sno = 0;
                                            while ($b_row = mysqli_fetch_assoc($result_blocked)) {
                                                $b_sno++;
                                                $status_badge = $b_row['is_blocked'] 
                                                    ? "<span class='badge bg-danger'><i class='fas fa-user-lock me-1'></i> Blocked</span>" 
                                                    : "<span class='badge bg-success'><i class='fas fa-check-circle me-1'></i> Active</span>";
                                                
                                                $action_btn = $b_row['is_blocked'] 
                                                    ? "<a href='user.php?toggle_block=" . $b_row['srno'] . "' class='btn btn-sm btn-outline-success w-100' style='border-radius: 8px;'><i class='fas fa-unlock me-1'></i> Activate User</a>" 
                                                    : "<a href='user.php?toggle_block=" . $b_row['srno'] . "' class='btn btn-sm btn-outline-warning w-100' style='border-radius: 8px;'><i class='fas fa-user-slash me-1'></i> Block User</a>";

                                                echo "<tr>
                                                        <td><strong>" . $b_sno . "</strong></td>
                                                        <td>" . htmlspecialchars($b_row['user_firstname'] . ' ' . $b_row['user_surname']) . "</td>
                                                        <td>" . htmlspecialchars($b_row['user_id']) . "</td>
                                                        <td>" . $status_badge . "</td>
                                                        <td class='action-btns'>" . $action_btn . "</td>
                                                      </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
                            } else {
                                echo '<div class="alert" style="background: rgba(255,255,255,0.05); color: #94a3b8; border-radius: 12px;"><i class="fas fa-info-circle me-2"></i> No users found.</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div class="modal fade" id="userEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="user.php" method="POST" enctype="multipart/form-data" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit User Profile</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <div class="image-preview-wrapper mx-auto" style="width: 120px; height: 120px; border-radius: 50%;">
                                    <img id="user_image_preview" src="" alt="Profile Preview">
                                </div>
                                <label class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-camera me-1"></i> Change Photo
                                    <input type="file" name="user_image" class="edit-image-input" data-target="#user_image_preview" hidden accept="image/*">
                                </label>
                            </div>

                            <input type="hidden" name="user_srno" id="edit_user_srno">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="user_firstname" id="edit_user_fname" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Surname</label>
                                    <input type="text" name="user_surname" id="edit_user_sname" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email / Phone</label>
                                <input type="text" name="user_id" id="edit_user_email" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">DOB</label>
                                    <input type="date" name="user_dob" id="edit_user_dob" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select name="user_gender" id="edit_user_gender" class="form-control" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_user" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Post Text Modal -->
            <div class="modal fade" id="postTextEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="user.php" method="POST" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-paragraph me-2"></i>Edit Post Caption</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="post_id" id="edit_post_text_id">
                            <div class="mb-3">
                                <label class="form-label">Caption</label>
                                <textarea name="post_caption" id="edit_post_caption" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_post_caption" class="btn btn-primary">Save Caption</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Post Media Modal -->
            <div class="modal fade" id="postMediaEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="user.php" method="POST" enctype="multipart/form-data" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-image me-2"></i>Change Post Media</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="image-preview-wrapper mb-3">
                                <img id="post_media_preview" src="" alt="Post Preview">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select New Media</label>
                                <input type="file" name="post_image" class="form-control edit-image-input" data-target="#post_media_preview" accept="image/*" required>
                                <small class="text-muted">Max size 2MB. Only images allowed.</small>
                            </div>
                            <input type="hidden" name="post_id" id="edit_post_media_id">
                            <input type="hidden" name="post_caption" id="keep_old_caption">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_post_media" class="btn btn-primary">Upload Media</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Story Media Modal -->
            <div class="modal fade" id="storyMediaEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="user.php" method="POST" enctype="multipart/form-data" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-photo-video me-2"></i>Change Story Media</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="image-preview-wrapper mb-3" id="story_media_preview_container">
                                <img id="story_media_preview" src="" alt="Story Preview" style="display:none;">
                                <video id="story_video_preview" controls style="display:none; max-width: 100%; max-height: 100%;"></video>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select New Media</label>
                                <input type="file" name="story_media" class="form-control story-edit-input" data-img-target="#story_media_preview" data-vid-target="#story_video_preview" accept="image/*,video/*" required>
                                <small class="text-muted">Images or videos (max 30s) allowed.</small>
                            </div>
                            <input type="hidden" name="story_id" id="edit_story_media_id">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_story_media" class="btn btn-primary">Update Media</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal fade" id="msgEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="user.php" method="POST" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-comment-dots me-2"></i>Edit Message</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="msg_id" id="edit_msg_id">
                            <div class="mb-3">
                                <label class="form-label">Message Content</label>
                                <textarea name="msg_content" id="edit_msg_content" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_msg" class="btn btn-primary">Update Message</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Story Modal -->
            <div class="modal fade" id="storyEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="user.php" method="POST" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-history me-2"></i>Edit Story Caption</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="story_id" id="edit_story_id">
                            <div class="mb-3">
                                <label class="form-label">Story Caption</label>
                                <textarea name="story_caption" id="edit_story_caption" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_story" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
            <script>
                $(document).ready(function () {
                    $('#usersTable, #postsTable, #storiesTable, #chatsTable').DataTable({
                        "pageLength": 10,
                        "language": {
                            "search": "Filter records:",
                        }
                    });

                    $('.delete').on('click', function(e) {
                        let sno = $(this).attr('id');
                        if (confirm("SECURITY WARNING: Are you entirely sure you want to permanently delete this user and all their affiliated data? This action cannot be undone.")) {
                            window.location = `user.php?delete=${sno}`;
                        }
                    });

                    $('.delete_post').on('click', function(e) {
                        let sno = $(this).attr('id');
                        if (confirm("Are you sure you want to delete this specific post?")) {
                            window.location = `user.php?delete_post=${sno}`;
                        }
                    });

                    $(document).on('click', '.delete_story', function() {
                        let sno = $(this).attr('id');
                        if (confirm("Are you sure you want to delete this specific story?")) {
                            window.location = `user.php?delete_story=${sno}`;
                        }
                    });

                    $('.delete_msg').on('click', function(e) {
                        let sno = $(this).attr('id');
                        if (confirm("Are you sure you want to delete this specific message?")) {
                            window.location = `user.php?delete_msg=${sno}`;
                        }
                    });

                    // Delegated Edit User Modal Triggers
                    $(document).on('click', '.edit-user', function() {
                        $('#edit_user_srno').val($(this).data('srno'));
                        $('#edit_user_fname').val($(this).data('fname'));
                        $('#edit_user_sname').val($(this).data('sname'));
                        $('#edit_user_email').val($(this).data('email'));
                        $('#edit_user_dob').val($(this).data('dob'));
                        $('#edit_user_gender').val($(this).data('gender'));
                        
                        // Set Image Preview
                        let userImg = $(this).data('image');
                        $('#user_image_preview').attr('src', '../post_img/' + userImg);
                        
                        $('#userEditModal').modal('show');
                    });

                    // Delegated Edit Post Text Modal Triggers
                    $(document).on('click', '.edit-post-text', function() {
                        $('#edit_post_text_id').val($(this).data('id'));
                        $('#edit_post_caption').val($(this).data('caption'));
                        $('#postTextEditModal').modal('show');
                    });

                    // Delegated Edit Post Media Modal Triggers
                    $(document).on('click', '.edit-post-media', function() {
                        $('#edit_post_media_id').val($(this).data('id'));
                        $('#keep_old_caption').val($(this).closest('tr').find('td:eq(3)').text().trim()); // Get from table row
                        
                        // Set Image Preview
                        let postImg = $(this).data('image');
                        $('#post_media_preview').attr('src', '../post_img/' + postImg);
                        
                        $('#postMediaEditModal').modal('show');
                    });

                    // Live Image Preview Function
                    $('.edit-image-input').on('change', function() {
                        const target = $(this).data('target');
                        if (this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                $(target).attr('src', e.target.result);
                            }
                            reader.readAsDataURL(this.files[0]);
                        }
                    });

                    // Delegated Edit Message Modal Triggers
                    $(document).on('click', '.edit-msg', function() {
                        $('#edit_msg_id').val($(this).data('id'));
                        $('#edit_msg_content').val($(this).data('content'));
                        $('#msgEditModal').modal('show');
                    });

                    // Delegated Edit Story Modal Triggers
                    $(document).on('click', '.edit-story', function() {
                        $('#edit_story_id').val($(this).data('id'));
                        $('#edit_story_caption').val($(this).data('caption'));
                        $('#storyEditModal').modal('show');
                    });

                    // Delegated Edit Story Media Modal Triggers
                    $(document).on('click', '.edit-story-media', function() {
                        const id = $(this).data('id');
                        const media = $(this).data('media');
                        const type = $(this).data('type');
                        
                        $('#edit_story_media_id').val(id);
                        
                        if (type === 'image') {
                            $('#story_media_preview').attr('src', '../' + media).show();
                            $('#story_video_preview').hide();
                        } else {
                            $('#story_video_preview').attr('src', '../' + media).show();
                            $('#story_media_preview').hide();
                        }
                        
                        $('#storyMediaEditModal').modal('show');
                    });

                    // Live Media Preview for Stories
                    $('.story-edit-input').on('change', function() {
                        const imgTarget = $(this).data('img-target');
                        const vidTarget = $(this).data('vid-target');
                        
                        if (this.files && this.files[0]) {
                            const file = this.files[0];
                            const reader = new FileReader();
                            
                            if (file.type.includes('video')) {
                                $(imgTarget).hide();
                                $(vidTarget).show();
                                $(vidTarget).attr('src', URL.createObjectURL(file));
                            } else {
                                $(vidTarget).hide();
                                $(imgTarget).show();
                                reader.onload = function(e) {
                                    $(imgTarget).attr('src', e.target.result);
                                }
                                reader.readAsDataURL(file);
                            }
                        }
                    });

                    // Post Filtering Logic
                    $('.filter-btn').on('click', function() {
                        const filter = $(this).data('filter');
                        
                        // Update active state
                        $('.filter-btn').removeClass('active');
                        $(this).addClass('active');
                        
                        // Filter the table rows
                        if (filter === 'all') {
                            $('.post-row').fadeIn(300);
                        } else {
                            $('.post-row').hide();
                            $(`.post-row[data-type="${filter}"]`).fadeIn(300);
                        }
                    });

                    // AUTO CLOSE ALERTS & CLEAN URL
                    if ($('.auto-close-alert').length > 0) {
                        setTimeout(function() {
                            $('.auto-close-alert').animate({
                                opacity: 0,
                                marginBottom: '-50px'
                            }, 600, function() {
                                $(this).remove();
                                // Clean the URL parameters and effectively "redirect" to clean home state
                                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                                window.history.replaceState({path: cleanUrl}, '', cleanUrl);
                            });
                        }, 2500); // Snappy 2.5 seconds
                    }
                });
            </script>
        </body>
        </html>
<?php
}
else 
{
    header('location:../login.php'); // Fixed path
    exit;
}
?>
