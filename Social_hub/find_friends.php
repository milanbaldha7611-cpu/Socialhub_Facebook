<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Friends | Social Hub</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/social_hub_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body class="light-mode">
    <?php include "navbar.php"; ?>

    <div class="profile_container" style="max-width: 800px; margin-top: 40px;">
        <h2 style="margin-bottom: 20px;">People You May Know</h2>
        <div class="find-friends-list">
            <?php
            $uid = $_SESSION['srno'];
            // Fetch all users except self, existing friends, and pending requests
            $q = "SELECT * FROM user_data WHERE srno != $uid 
                      AND srno NOT IN (SELECT receiver_id FROM friend_request WHERE sender_id=$uid)
                      AND srno NOT IN (SELECT sender_id FROM friend_request WHERE receiver_id=$uid)
                      AND srno NOT IN (SELECT blocked_user_id FROM user_blocks WHERE blocker_id=$uid)
                      AND srno NOT IN (SELECT blocker_id FROM user_blocks WHERE blocked_user_id=$uid)
                      ORDER BY RAND()"; // Get more users
            $res = mysqli_query($conn, $q);

            if (mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $profile_img = $row['user_image'] ? "post_img/" . $row['user_image'] : "icon/friend_dp.jpeg";
                    $name = $row['user_firstname'] . ' ' . $row['user_surname'];
                    $frnd_url = "other_user_profile.php?user_srno=" . $row['srno'];
                    ?>
                    <div class="card"
                        style="padding: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; border-radius: 16px;">
                        <div style="display: flex; align-items: center; gap: 20px; flex: 1 1 min-content;">
                            <a href="<?php echo $frnd_url; ?>">
                                <img src="<?php echo $profile_img; ?>" alt="User"
                                    style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-color);">
                            </a>
                            <div>
                                <a href="<?php echo $frnd_url; ?>" style="text-decoration: none; color: inherit;">
                                    <h3 style="font-size: 20px; margin-bottom: 5px;"><?php echo $name; ?></h3>
                                </a>
                                <p style="color: var(--color2); font-size: 14px;">Suggested for you</p>
                            </div>
                        </div>

                        <button class="add_friend_btn"
                            style="width: auto; padding: 10px 25px; margin: 0; font-size: 15px; flex: 0 0 auto; min-width: 140px; display: flex; gap: 8px;"
                            data-user-srno="<?php echo $row['srno']; ?>" data-request-status="not_sent">
                            <img src="icon/add-user.svg" style="height: 18px; filter: invert(1); margin-right: 5px;"
                                alt="Add User"> Add Friend
                        </button>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='card' style='padding: 30px; text-align: center;'><p style='font-size: 18px; color: var(--color2);'>No new suggestions right now. You are connected with everyone!</p></div>";
            }
            ?>
        </div>
    </div>
</body>

</html>