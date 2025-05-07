<?php
    session_start();
    require_once 'config.php';

    $adminpass_md5 = getenv('adminpass_md5');
    //退出
    if (isset($_GET["action"]))
    {
        if ($_GET["action"] == "logout")
        {
            unset($_SESSION['adminname']);
            setcookie("userInfo", "", time()-3600);
            //$_SESSION['Admin'] = "";
            header("location:index.php");
            exit();
        }
    }

    $adminname = $_POST["username"];
    $adminpass = $_POST["password"];
    //检查user是否在数据库中,否则添加

    //判断是否为空
    if ($adminname == "" || $adminpass == "")
    {
        //echo $adminname;
        echo "<script>alert('用户名和密码不能为空！');window.location.href = 'index.php';</script>";
        //header("location:index.php");
        exit();
    }
    else
    {
        $adminpass = md5($adminpass);
        //判断用户身份是否为管理员
        if ($adminname == "admin" && $adminpass == $adminpass_md5)
        {
            //如果是管理员，并且用户名是Rarin,那么则把他们输入进session里
            $_SESSION["adminname"] = $adminname;
            $_SESSION["adminpass"] = $adminpass;
            if($_POST["remember"] === "1")
            {
                $expire=time()+60*60*24*30;
                setcookie("userInfo", md5($adminpass_md5), $expire);
            }

            header("location:show.php");//成功后返回show.php页面并保存admin值
            exit();
        }
        else
        {
            header("location:index.php");//成功后返回show.php页面并保存admin值
            exit();
        }
    }

?>