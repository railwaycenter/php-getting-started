<?php
    require '_session.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>增加站点</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
        require 'headcss.php';
    ?>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">请输入站点信息</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form onsubmit="return false">
                    <div class="card-body">
                        <input type="text" class="form-control" id="site_name" placeholder="站点名">
                        <br>
                        <input type="text" class="form-control" id="site_url" placeholder="站点url">
                        <br>
                        <input type="text" class="form-control" id="site_userclass" placeholder="永久保留账号等级">
                        <br>
                        <input type="text" class="form-control" id="site_userclassinfo" placeholder="永久保留账号等级信息">
                        <br>
                        <input type="text" class="form-control" id="site_download" placeholder="站点下载量(单位TB)">
                        <br>
                        <input type="text" class="form-control" id="site_ratio" placeholder="站点分享率">
                        <br>
                        <input type="text" class="form-control" id="current_class" placeholder="当前账号等级">
                        <br>
                        <input type="text" class="form-control" id="current_class_info" placeholder="当前账号等级信息">
                        <br>
                        <input type="text" class="form-control" id="current_upload" placeholder="上传量(单位TB)">
                        <br>
                        <input type="text" class="form-control" id="current_download" placeholder="下载量(单位TB)">
                        <br>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="btnConfirm">提交</button>
                        <button type="reset" class="btn btn-primary">重置</button>
                        <span id="message"></span>
                        <a href="pt.php" class="btn btn-success float-right">显示站点信息</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
    require 'footjs.php';
?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#btnConfirm").click(
            function () {
                var site_name = $("#site_name").val();
                var site_url = $("#site_url").val();

                if (site_name.length <= 0)
                {
                    $("#site_name").addClass("is-invalid");
                    return false;
                }
                else
                {
                    $("#site_name").removeClass("is-invalid");
                }

                if (site_url.length <= 0)
                {
                    $("#site_url").addClass("is-invalid");
                    return false;
                }
                else
                {
                    $("#site_url").removeClass("is-invalid");
                }

                $.post("ptsave.php", {
                        field: "addsite",
                        site_name: site_name,
                        site_url: site_url,
                        site_userclass: $("#site_userclass").val(),
                        site_userclassinfo: $("#site_userclassinfo").val(),
                        site_download: $("#site_download").val(),
                        site_ratio: $("#site_ratio").val(),
                        current_class: $("#current_class").val(),
                        current_class_info: $("#current_class_info").val(),
                        current_upload: $("#current_upload").val(),
                        current_download: $("#current_download").val(),
                    },
                    function (data) {
                        $("#message").text('数据添加完成。序号：' + (data.code));
                        $("#site_name,#site_url,#site_userclass,#site_userclassinfo,#site_download,#site_ratio,#current_class,#current_class_info,#current_upload,#current_download").val("");
                    },
                    "json");

            });
    });
</script>
</body>
</html>