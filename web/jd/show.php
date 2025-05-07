<?php
    require '_session.php';
    require 'Page.func.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>显示监控商品</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="3600">
    <?php
        require 'headcss.php';
    ?>
</head>
<body>
<div class="container">
    <div class="row justify-content-center mt-3">
        <div class="table-responsive">
            <table id="item-list" class="table table-bordered table-striped table-hover">
                <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th class="w-50">商品名</th>
                    <th>现价</th>
                    <th>预期价格</th>
                    <th>商城名称</th>
                    <th width="40px">是否监控</th>
                </tr>
                </thead>

                <tbody>
                <?php
                    require 'config.php';
                    $limit     = isset($_GET["limit"]) ? $_GET["limit"] : 20;
                    $page      = isset($_GET["page"]) ? $_GET["page"] : 1;
                    $pageStart = ($page - 1) * $limit;//数组以0起始
                    //echo $page,"==============",$limit;
                    $datas = $database->select("monitor", "*", ["ORDER" => ["id" => "DESC"],
                                                                'LIMIT' => [$pageStart,
                                                                            $limit]]);
                    foreach ($datas as $data)
                    {
                        if ($data["status"] == 1)
                        {
                            $statusSwitch = "<div class=\"custom-control custom-switch custom-switch-off-danger custom-switch-on-success\">
    <input type=\"checkbox\" checked class=\"custom-control-input\" id=\"customSwitch{$data["id"]}\" checkid=\"1\">
    <label class=\"custom-control-label\" for=\"customSwitch{$data["id"]}\"></label>
</div>";
                        }
                        else
                        {
                            $statusSwitch = "<div class=\"custom-control custom-switch custom-switch-off-danger custom-switch-on-success\">
    <input type=\"checkbox\" class=\"custom-control-input\" id=\"customSwitch{$data["id"]}\"  checkid=\"0\">
    <label class=\"custom-control-label\" for=\"customSwitch{$data["id"]}\"></label>
</div>";
                        }
                        echo "<tr id='", $data["id"], "'> \n";
                        echo "<th>", $data["id"], "</th> \n";
                        echo "<th class='item_name' field=\"item_name\"><a href='{$data["item_url"]}' target='_blank'>", $data["item_name"] ? $data["item_name"] : $data["item_url"], "</a></th> \n";
                        echo "<th class='item_price' field=\"item_price\">", $data["item_price"], "</th> \n";
                        echo "<th class='user_price' field=\"user_price\">", $data["user_price"], "</th> \n";
                        echo "<th class='mall_name' field=\"mall_name\">", $data["mall_name"], "</th> \n";
                        echo "<th class='status' field=\"status\">", $statusSwitch, "</th> \n";
                        echo "</tr> \n";
                    }
                    $PageNav = multi($database->count("monitor"), $limit, $page, 'show.php');

                ?>

                </tbody>

                <tfoot>
                <tr>
                    <th>ID</th>
                    <th>商品名</th>
                    <th>现价</th>
                    <th>预期价格</th>
                    <th>商城名称</th>
                    <th width="40px">是否监控</th>
                </tr>
                </tfoot>
            </table>
        </div>
        <div class="col mb-3" id="nav">
            <?php
                echo default_css();
                echo $PageNav;
            ?>
        </div>
    </div>

    <div class="row  justify-content-center mb-3">
        <div class="input-group input-group-sm">
            <input class="form-control form-control-navbar" type="search" placeholder="请输入关键字搜索" aria-label="Search" id="keywords" maxlength="26">
            <div class="input-group-append">
                <button class="btn btn-navbar" type="submit" id="searchsm">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="invalid-feedback">请输入有效关键字搜索</div>
        </div>
    </div>

    <div class="row justify-content-between">
        <div class="col-2  text-left">
            <a href="login.php?action=logout" class="btn btn-danger">退出登录</a>
        </div>
        <div class="col-1  text-left">
            <a href="https://app.infinityfree.net/accounts" class="btn btn-danger" target="_blank">infinityfree</a>
        </div>
        <div class="col-5  text-right">
            <a href="show.php" class="btn btn-primary">返回首页</a>
        </div>
        <div class="col-2  text-right">
            <a href="addProduct.php" class="btn btn-success">增加监控商品</a>
        </div>

        <div class="col-2  text-right">
            <a href="jdconfig.php" class="btn btn-success">京东配置页</a>
        </div>
    </div>

    <div class="row d-none" id="priceCharts">
        <div class="col-12">
            <!-- 为 ECharts 准备一个具备大小（宽高）的 DOM -->
            <div id="main" style="height:400px;"></div>
        </div>
    </div>
</div>

<?php
    require 'footjs.php';
?>
<script src="js/main.js"></script>

</body>
</html>