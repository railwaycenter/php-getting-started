<?php
    require '_session.php';
    require 'config.php';

    //require 'Logger.php';
    //$logHandler = new CLogFileHandler(__DIR__ . '/logs/phpjd' . date('Y-m-d') . '.log');

    //$log = Log::Init($logHandler, 15);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>京东配置信息</title>
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
                    <h3 class="card-title">京东配置信息</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <table id="item-list" class="table table-bordered table-striped table-hover">
                    <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>key</th>
                        <th>值</th>
                        <th>说明</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                        $datas = $database->select("monitor_config", "*", ["ORDER" => ["id" => "ASC"]]);
                        foreach ($datas as $data)
                        {
                            echo "<tr id='", $data["id"], "'> \n";
                            echo "<th>", $data["id"], "</th> \n";
                            echo "<th class='jdkey' field=\"jdkey\">",$data["key"],"</th> \n";
                            echo "<th class='jdvalue' field=\"jdvalue\">", $data["value"], "</th> \n";
                            echo "<th class='jdcomment' field=\"jdcomment\">", $data["comment"], "</th> \n";
                            echo "</tr> \n";
                        }
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <a href="show.php" class="btn btn-success float-right">显示监控商品</a>
            </div>
        </div>
    </div>
</div>

<?php
    require 'footjs.php';
?>
<script type="text/javascript">
    $(document).ready(function () {
        var originaldata = {};
        $("#item-list").on("click",".jdvalue",function() {
            $('.jdvalue').editable('save.php', {
                // id   : 'bookId',
                // name : 'user_price',
                indicator: 'Saving…',
                event: 'dblclick',
                //cssclass  : 'custom-css',
                submit: '保存',
                cancel: '取消',
                cancelcssclass: 'btn btn-danger',
                submitcssclass: 'btn btn-success',
                tooltip: '双击编辑',
                indicator: '正在保存',
                select: true,
                formid: 'formid',
                placeholder: '',
                onedit: function () {
                    console.log('If I return false edition will be canceled');
                    return true;
                },
                before: function () {
                    console.log('Triggered before form appears');
                    //console.log('oldvalue'+oldvalue);
                },
                onsubmit: function (settings, original) {
                    console.log('Triggered before submit');
                    console.log('refert' + original.revert);
                    //console.log(settings);
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

                callback: function (result, settings, submitdata) {
                    console.log('Triggered after submit');
                    console.log('Result: ' + result);
                    //console.log('Settings.width: ' + settings.width);
                    console.log('Submitdata: ' + submitdata.field);
                    console.log('Submitdata: ' + submitdata.oldvalue);
                },
                //onblur: "ignore",
                //submitdata : submitdata,
                //submitdata as a function example
                submitdata: function (oldValue, settings) {
                    originaldata['oldvalue'] = oldValue;
                    console.log("oldValue text: " + oldValue);
                    //console.log(settings);
                    //console.log("User submitted text: " + submitdata.value);
                    return {
                        id: $(this).parent().attr('id'),
                        field: $(this).attr('field'),
                        oldvalue: oldValue
                    }
                },

            });
        });
    });
</script>
</body>
</html>
