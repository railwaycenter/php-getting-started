<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>B站直连</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.14.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.10.21/css/dataTables.bootstrap4.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/css/adminlte.min.css">
</head>
<body>
<div class="container">
    <div class="row mb-3">
        <div class="col">
            <div id="dplayer"></div>
        </div>
    </div>
    <div class="row ">
        <div class="col">
            <input type="text" name="bid" id="bid" placeholder="请输入B站id" />
            <button class="btn btn-success" id="btnConfirm" type="submit">提交</button>
        </div>
    </div>
    <?php
        $header = array('Host:api.live.bilibili.com', 'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0', 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'Accept-Encoding:gzip, deflate, br');

        $headerm = array('Host:item.m.jd.com', 'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0', 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'Accept-Encoding:gzip, deflate, br');


        function getUrl($url, $header = false)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回数据不直接输出
            //curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate, br"); //指定gzip压缩
            curl_setopt($ch, CURLOPT_ENCODING, "gzip"); //指定gzip压缩
            //add header
            if (!empty($header))
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            //add ssl support
            if (substr($url, 0, 5) == 'https')
            {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
            }
            //add 302 support
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            //curl_setopt($ch,CURLOPT_COOKIEFILE, $this->lastCookieFile); //使用提交后得到的cookie数据
            try
            {
                $content = curl_exec($ch); //执行并存储结果
            }
            catch (Exception $e)
            {
                //$this->_log($e->getMessage());
                echo "try";
                echo $e->getMessage();
            }
            $curlError = curl_error($ch);
            if (!empty($curlError))
            {
                //$this->_log($curlError);
                echo "curlError";
                echo $curlError;
            }
            curl_close($ch);
            //$output = json_decode($content,true);
            //return $output;
            return $content;
        }

        $roomid = isset($_GET["id"]) ? $_GET["id"] : "6";
        //$url = "https://api.live.bilibili.com/xlive/web-room/v1/index/getRoomPlayInfo?room_id=6&play_url=1&mask=1&qn=4&platform=web";
        $url  = "https://api.live.bilibili.com/xlive/web-room/v1/index/getRoomPlayInfo?room_id={$roomid}&play_url=1&mask=1&qn=4&platform=web";
        $str  = json_decode(getUrl($url), true);
        $durl = $str["data"]["play_url"]["durl"];


        //var_dump( $durl);
        
        $firstUrl = $durl[0]["url"];
        
        //echo $firstUrl;
        echo "<div class='row'> \n";
        echo "<div class='col text-break'>原始链接：<a href=\"$url\" target=\"_blank\">复制后再打开</a></div> \n";
        echo "</div><br> \n";

        foreach ($durl as $name => $value)
        {
            echo "<div class='row'> \n";
            echo "<div class='col text-break'>", $value["url"], "</div> \n";
            echo "</div><br> \n";
        }

    ?>

</div>

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.1/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.0.5/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-jeditable@2.0.17/src/jquery.jeditable.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.10.21/js/dataTables.bootstrap4.min.js"></script>
<!-- 引入 ECharts 文件 -->
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.4.0/dist/js/bootstrap-switch.min.js"></script>-->


<script src="https://cdn.jsdelivr.net/npm/hls.js@0.14.7/dist/hls.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flv.js@1.5.0/dist/flv.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dplayer@1.26.0/dist/DPlayer.min.js"></script>
<script>
    const dp = new DPlayer({
        container: document.getElementById('dplayer'),
        live: true,
        video: {
            url: '<?php echo $firstUrl?>',
            //url:'https://api.dogecloud.com/player/get.m3u8?vcode=5ac682e6f8231991&userId=17&ext=.m3u8',
            type: 'auto',
        },
    });

    $(document).ready(function () {
        $("#bid").focus();
        $("#bid").keydown(function(e){
            if(e.keyCode == 13){
                $('#btnConfirm').trigger("click");
            }
        });

        $("#btnConfirm").click(function () {
            window.location.replace(window.location.protocol+"//"+window.location.host + window.location.pathname + "?id=" + $("#bid").val())
            //alert(window.location.href + "?id=" + $("#bid").val());
            //alert(window.location.host);
        });
    });
</script>
</body>
</html>
