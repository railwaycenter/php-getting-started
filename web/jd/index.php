<?php
    session_start();
    //  判断是否登陆
    if (isset($_SESSION["adminname"]) || $_COOKIE["userInfo"] == md5(getenv('adminpass_md5')))
    {
        header("location:show.php");
        exit();
    }

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>登录</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
        require 'headcss.php';
    ?>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <b>管理</b>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">登录</p>

            <form action="login.php" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="用户名">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="密码">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" value="1">
                            <label for="remember">
                                记住我
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">登录</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
            <!--            <div class="social-auth-links text-center mb-3">-->
            <!--                <p>- OR -</p>-->
            <!--                <a href="#" class="btn btn-block btn-primary">-->
            <!--                    <i class="fab fa-facebook mr-2"></i> Sign in using Facebook-->
            <!--                </a>-->
            <!--                <a href="#" class="btn btn-block btn-danger">-->
            <!--                    <i class="fab fa-google-plus mr-2"></i> Sign in using Google+-->
            <!--                </a>-->
            <!--            </div>-->
            <!-- /.social-auth-links -->
            <!---->
            <!--            <p class="mb-1">-->
            <!--                <a href="forgot-password.html">I forgot my password</a>-->
            <!--            </p>-->
            <!--            <p class="mb-0">-->
            <!--                <a href="register.html" class="text-center">Register a new membership</a>-->
            <!--            </p>-->
        </div>
        <!-- /.login-card-body -->
    </div>
</div>

<?php
    require 'footjs.php';
?>
<script type="text/javascript">
    $(document).ready(function () {
        $("[name=username]").focus();

    });
</script>
</body>
</html>