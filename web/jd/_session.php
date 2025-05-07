<?php
    session_start();
    //  判断是否登陆
    if (!isset($_SESSION["adminname"]) && $_COOKIE["userInfo"] != md5(getenv('adminpass_md5')))
    {
        header("location:index.php");
        exit();
    }
?>