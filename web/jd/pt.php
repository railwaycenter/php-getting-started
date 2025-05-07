<?php
    //require '_session.php';
    require 'Page.func.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>PT站永久会员信息</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                    <th>站名</th>
                    <th>永保等级</th>
                    <th>永保信息</th>
                    <th>下载</th>
                    <th>上传</th>
                    <th width="40px">实际下载</th>
                    <th width="40px">实际上传</th>
                    <th width="40px">已达永保</th>
                    <th width="100px">下载说明</th>
                </tr>
                </thead>

                <tbody>
                <?php
                    require 'config.php';
                    $limit     = $_GET["limit"] ? $_GET["limit"] : 10;
                    $page      = $_GET["page"] ? $_GET["page"] : 1;
                    $pageStart = ($page - 1) * $limit;//数组以0起始
                    //echo $page,"==============",$limit;
                    $datas = $database->select("siteinfo", "*", ["ORDER" => ["id" => "DESC"], 'LIMIT' => [$pageStart, $limit]]);
                    foreach ($datas as $data)
                    {
                        if ($data["current_download"] > $data["site_download"] && $data["current_upload"] / $data["current_download"] > $data["site_ratio"])
                        {
                            $keep_account = "<span class='text-green'>达成</span>";
                        }
                        else
                        {
                            $keep_account = "<span class='text-red'>未达成</span>";
                        }
                        echo "<tr id='", $data["id"], "'> \n";
                        echo "<th>", $data["id"], "</th> \n";
                        echo "<th><a href='{$data["site_url"]}' target='_blank'>", $data["site_name"], "<a/></th> \n";
                        echo "<th>", $data["site_userclass"], "</th> \n";
                        echo "<th>", $data["site_userclassinfo"], "</th> \n";
                        echo "<th class='dl' field=\"dl\">", $data["site_download"], "</th> \n";
                        echo "<th>", $data["site_download"] * $data["site_ratio"], "</th> \n";
                        echo "<th class='cdl' field=\"cdl\">", $data["current_download"], "</th> \n";
                        echo "<th class='cul' field=\"cul\">", $data["current_upload"], "</th> \n";
                        echo "<th>", $keep_account, "</th> \n";
                        echo "<th class='desc' field=\"desc\">", $data["description"], "</th> \n";
                        echo "</tr> \n";
                    }
                    $PageNav = multi($database->count("siteinfo"), $limit, $page, 'pt.php');

                ?>

                </tbody>

                <tfoot>
<!--                <tr>-->
<!--                                        <th>ID</th>-->
<!--                    <th>站名</th>-->
<!--                    <th>永保等级</th>-->
<!--                    <th>永保信息</th>-->
<!--                    <th>下载</th>-->
<!--                    <th>上传</th>-->
<!--                    <th>实际下载</th>-->
<!--                    <th>实际上传</th>-->
<!--                    <th>已达永保</th>-->
<!--                </tr>-->
                </tfoot>
            </table>
        </div>
        <div class="col mb-3">
            <?php
                echo default_css();
                echo $PageNav;
            ?>
        </div>
    </div>

    <div class="row justify-content-between">
        <div class="col-2  text-left">
            <a href="login.php?action=logout" class="btn btn-danger">退出登录</a>
        </div>
        <div class="col-2  text-right">
            <a href="addSite.php" class="btn btn-success">增加监控站点</a>
        </div>
    </div>

</div>
<?php
    require 'footjs.php';
?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#item-list").DataTable({
            "language": {
                "info": "显示 _START_ - _END_ 总 _TOTAL_ 记录",
                "lengthMenu": "显示 _MENU_ 记录",
                "search": "搜索:",
                "paginate": {
                    "first": "首页",
                    "last": "尾页",
                    "next": "下一页",
                    "previous": "上一页"
                },
                "processing": "正在搜索"
            },
            "order": [[0, "desc"]],
            "paging": false,
            "info": false,
            // "pageLength": 10,
            // "processing": true,
            // "serverSide": true,
            // "ajax": "getitems.php",
            "autoWidth": false,
        });


        //实际上传下载量比较
        $("#item-list tr").each(function () {
            var dl = $(this).find("th").eq('4').text();
            var adl = $(this).find("th").eq('6').text();
            var ul = $(this).find("th").eq('5').text();
            var aul = $(this).find("th").eq('7').text();
            //console.log(adl - dl);
            if (adl - dl > 0)
            {
                $(this).find("th").eq('6').addClass("text-green");
            }
            if (aul - ul > 0)
            {
                $(this).find("th").eq('7').addClass("text-green");
            }
        });


        //修改实际下载和上传值
        $('.dl,.cdl,.cul,.desc').editable('ptsave.php', {
            indicator: 'Saving…',
            event: 'dblclick',
            submit: '保存',
            cancel: '取消',
            cancelcssclass: 'btn btn-danger',
            submitcssclass: 'btn btn-success',
            tooltip: '双击编辑',
            indicator: '正在保存',
            select: true,
            formid: 'formid',
            placeholder: '',
            onsubmit: function (settings, original) {
                console.log('refert' + original.revert);
                //简单验证
                var newValue = $(original).find('input').val();
                newValue = $.trim(newValue);
                if (newValue == original.revert)
                {
                    console.log("need not same");
                    $("#formid").append('<span class="text-red">请输入不同值</span>');
                    return false;
                }
                if (newValue == '')
                {
                    console.log('不能为空！');
                    return false;
                }
            },
            submitdata: function (oldValue, settings) {
                return {
                    id: $(this).parent().attr('id'),
                    field: $(this).attr('field'),
                    oldvalue: oldValue
                }
            },

        });

    });
</script>
</body>
</html>