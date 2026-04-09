<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends | Social Hub</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/social_hub_logo.png">
</head>

<body class="light-mode">
    <?php include "navbar.php"; ?>

    <div class="profile_container">

        <!-- Toggle Buttons -->
        <div class="friends-toggle-bar">
            <button class="friends-toggle-btn active" id="btn-pending">
                <i class="fas fa-user-clock"></i> Pending Requests
            </button>
            <button class="friends-toggle-btn" id="btn-my-friends">
                <i class="fas fa-user-friends"></i> My Friends
            </button>
        </div>

        <!-- Pending Requests Section -->
        <div id="section-pending">
            <div id="frnd_req_card"></div>
        </div>

        <!-- My Friends Section -->
        <div id="section-my-friends" style="display:none;">
            <div id="friends_list_card"></div>
        </div>

    </div>

    <script>
    $(document).ready(function () {

        // Load Pending Requests
        function loadPendingRequests() {
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: { action: 'Show_Pending_frnd_req_card' },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        $('#frnd_req_card').html(response.html);
                    } else {
                        $('#frnd_req_card').html(response.no_req);
                    }
                },
                error: function () {
                    $('#frnd_req_card').html('<p class="no_req_msg">Failed to load requests.</p>');
                }
            });
        }

        // Load My Friends
        function loadMyFriends() {
            $('#friends_list_card').html('<p class="no_req_msg" style="opacity:0.6;">Loading...</p>');
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: { action: 'get_friend_list' },
                dataType: "json",
                success: function (response) {
                    var container = $('#friends_list_card');
                    container.empty();

                    var all = [];
                    if (response.html_friend1) all = all.concat(response.html_friend1);
                    if (response.html_friend2) all = all.concat(response.html_friend2);

                    if (all.length === 0) {
                        container.html('<p class="no_req_msg">You have no friends yet. <a href="find_friends.php" style="color:#3b82f6;">Find Friends</a></p>');
                    } else {
                        all.forEach(function (html) {
                            container.append(html);
                        });
                    }
                },
                error: function () {
                    $('#friends_list_card').html('<p class="no_req_msg">Failed to load friends.</p>');
                }
            });
        }

        // Default load
        loadPendingRequests();

        // Toggle logic
        $('#btn-pending').on('click', function () {
            $(this).addClass('active');
            $('#btn-my-friends').removeClass('active');
            $('#section-pending').show();
            $('#section-my-friends').hide();
            loadPendingRequests();
        });

        $('#btn-my-friends').on('click', function () {
            $(this).addClass('active');
            $('#btn-pending').removeClass('active');
            $('#section-my-friends').show();
            $('#section-pending').hide();
            loadMyFriends();
        });

    });
    </script>

</body>

</html>