<?php
    require '_session.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>增加监控商品</title>
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
                    <h3 class="card-title">请输入商品信息</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form onsubmit="return false">
                    <div class="card-body">
                        <input type="text" class="form-control" id="item_url" placeholder="商品地址url">
                        <br>
                        <input type="text" class="form-control" id="user_price" placeholder="期望价格">
                        <br>
                        <input type="text" class="form-control" id="item_name" placeholder="商品名">
                        <br>
                        <select id="mall_name" class="form-control">
                            <option value="京东">京东</option>
                            <option value="淘宝">淘宝</option>
                            <option value="天猫">天猫</option>
                        </select>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="btnConfirm">提交</button>
                        <button type="reset" class="btn btn-primary">重置</button>
                        <span id="message"></span>
                        <a href="show.php" class="btn btn-success float-right">显示监控商品</a>
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
                $("#btnConfirm").attr("disabled",true);
                var item_url = $("#item_url").val();
                var user_price = $("#user_price").val();
                var mall_name = $("#mall_name").val();
                var item_name = $("#item_name").val();

                if (item_url.length <= 0)
                {
                    $('#btnConfirm').removeAttr("disabled");
                    $("#item_url").addClass("is-invalid");
                    return false;
                }
                else
                {
                    $("#item_url").removeClass("is-invalid");
                }

                if (user_price.length <= 0)
                {
                    $('#btnConfirm').removeAttr("disabled");
                    $("#user_price").addClass("is-invalid");
                    return false;
                }
                else
                {
                    $("#user_price").removeClass("is-invalid");
                }

                $.post("addProductAction.php", "url=" + item_url + "&price=" + user_price + "&mallname=" + mall_name + "&itemname=" + item_name, function (data) {
                    if(data.code == '100')
                    {
                        $("#message").text('该数据已存在，请勿重复添加数据');
                    }
                    else
                    {
                        $("#message").text('数据添加完成。序号：' + (data.addid));
                    }
                    $('#btnConfirm').removeAttr("disabled");
                    $("#item_url").val("");
                    $("#user_price").val("");
                }, "json");

            });
    });
</script>
</body>
</html>