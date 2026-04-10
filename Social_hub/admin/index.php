<?php

session_start();

if( isset( $_SESSION['user_id'] ) )
{
    header('location:user.php');
}
else
{
    header('location:login.php');
}
exit;