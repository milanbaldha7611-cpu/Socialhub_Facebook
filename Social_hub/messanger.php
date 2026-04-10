



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Social Hub</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/social_hub_logo.png">

</head>

<body class="light-mode">
    <?php include "navbar.php"; ?>
    <?php
        $loggedInUserId = $_SESSION['srno'];
    ?>
    <div class="container2 clearfix">
        <div class="people-list" id="people-list">
            <div class="search">
                <input type="text" placeholder="search" />
                <i class="fa fa-search"></i>
            </div>

            <ul class="list">

            </ul>

        </div>

        <div class="chat">
            <div class="chat-header">

            </div>

            <div class="chat-history">
                <ul>

                </ul>
            </div>

            <div class="chat-message clearfix">
                <form action="">
                    <textarea name="message-to-send" id="message-to-send" placeholder="Type your message"
                        rows="2"></textarea>
                    <input type="button" id="send-btn" value="Send" />
                </form>
            </div>
        </div>
    </div>


</body>

</html>