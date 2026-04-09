<?php
session_start();
include "_db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$userId = $_SESSION['user_id'];
$userSrno = $_SESSION['srno'];
$firstName = $_SESSION['user_firstname'];
$surname = $_SESSION['user_surname'];
$dob = $_SESSION['user_dob'];
$gender = $_SESSION['user_gender'];
$userCreate = $_SESSION['user_create'];
$userimage = $_SESSION['user_image'];

?>
<script>
    const CURRENT_USER_SRNO = '<?php echo $_SESSION['srno']; ?>';
</script>
<header>
    <nav>
        <div class="nav1">
            <a href="welcome.php"><img class="facebook_icon" src="img/social_hub_logo.png" alt="Social Hub"></a>
            <button id="hamburger-menu" class="hamburger-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div id="nav-menu" class="nav-menu">
            <div class="nav2">
            <a href="welcome.php"><img src="icon/home.png" alt="Home"></a>
            <a href="friends.php"><img src="icon/groups.png" alt="Group"></a>
            <a href="messanger.php"><img src="icon/messenger.png" alt="Chat"></a>
            <a href="find_friends.php"><img src="icon/add-user.svg" alt="Find Friends"></a>
        </div>

        <div class="nav3">

            <!-- HTML button with unread notification count -->
            <button data-notification-count="" id="notification-btn">
                <img src="icon/bell-ring_3306630.png" />
            </button>
            <div class="user_icon">
                <a href="profile.php"><img src="post_img/<?php echo $_SESSION['user_image']; ?>" alt="User"></a>
                <h4>
                    <?php echo $firstName;
                    echo ' <br>';
                    echo $surname; ?>
                </h4>
            </div>
            <form action="logout.php" method="post">
                <input type="submit" value="Logout" id="logout" name="logout">
            </form>
        </div>
        </div>
    </nav>
    <div class="notification_box">
        <div class="notificaton_area">
            <ul id="notification-list">
                <li data-notification-id="">

                </li>

            </ul>
        </div>
    </div>


</header>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var hamburger = document.getElementById('hamburger-menu');
    var navMenu = document.getElementById('nav-menu');
    if(hamburger && navMenu) {
        hamburger.addEventListener('click', function(e) {
            e.preventDefault();
            navMenu.classList.toggle('active');
        });
    }
});
</script>
<script src="theme.js"></script>