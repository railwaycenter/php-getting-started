<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>虎牙直连</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/bootstrap/4.6.1/css/bootstrap.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-10-y/datatables/1.10.21/css/dataTables.bootstrap4.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/tarekraafat-autocomplete.js/10.2.6/css/autoComplete.min.css">



    <style>
        /* 自定义提示框样式 */
        #message-box {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            z-index: 1000;
        }
    </style>
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
            <input type="search" name="bid" id="bid" list="appNamelist" placeholder="请输入虎牙id"/>
<!--            <datalist id="appNamelist">-->
<!--                <option value="859042">正恒-紫宸【相声木兰】</option>-->
<!--                <option value="330679">怀逝【李白导师】</option>-->
<!--                <option value="391946">小炎【妲己的神】</option>-->
<!--                <option value="691346">宇晨【马可导师】</option>-->
<!--                <option value="825912">念青【嘴强王者】</option>-->
<!--                <option value="651353">久爱-猪猪小悠</option>-->
<!---->
<!--            </datalist>-->
            <button class="btn btn-success" id="btnConfirm" type="submit">提交</button>
            <a href="https://www.huya.com/g/wzry#cate-0-0" target="_blank">虎牙直播地址</a>
            <a href="pg.php" target="_blank">直播管理地址</a>
        </div>
    </div>

    <div class="row ">
        <div class="col">
            <input type="text" name="roomId" id="roomId" placeholder="请输入虎牙id"/>
            <input type="text" name="roomName" id="roomName" placeholder="请输入虎牙房间名"/>
            <button class="btn btn-success" id="btnSubmit" type="submit">本地保存</button>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <input type="text" id="room_id" placeholder="请输入虎牙id">
            <input type="text" id="room_name" placeholder="请输入虎牙房间名">
            <button class="btn btn-success" id="save_button">网络保存</button>
        </div>
    </div>

    <!-- 自定义提示框 -->
    <div id="message-box"></div>

    <?php
        date_default_timezone_set("Asia/Shanghai");
        $type = empty($_GET['type']) ? "nodisplay" : trim($_GET['type']);
        $url = empty($_GET['id']) ? "391946" : trim($_GET['id']);
        // 检查URL中是否包含数字
        if (preg_match('/\d+/', $url, $match))
        {
            // 使用正则表达式提取所有数字
            //preg_match('/\d+/', $url, $matches);

            // 输出结果
            $id = $match[0];
            // print_r($id);
        }
        else
        {
            $id = $url;
        }
        // print_r($id);
        $cdn = empty($_GET['cdn']) ? "hwcdn" : trim($_GET['cdn']);
        $media = empty($_GET['media']) ? "flv" : trim($_GET['media']);
        $roomurl = "https://mp.huya.com/cache.php?m=Live&do=profileRoom&roomid=" . $id;


        function get_content($apiurl, $flag)
        {
            if ($flag == "mobile")
            {
                $headers = array(
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Mobile/15E148 Safari/604.1'
                );
            }
            else
            {
                $arr = [
                    "appId" => 5002,
                    "byPass" => 3,
                    "context" => "",
                    "version" => "2.4",
                    "data" => new stdClass(),
                ];
                $postData = json_encode($arr);
                $headers = array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($postData),
                    'upgrade-insecure-requests: 1',
                    'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36'
                );
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            if ($flag == "uid")
            {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
            $data = curl_exec($ch);
            curl_close($ch);

            return $data;
        }

        $jsonStr = json_decode(get_content($roomurl, "mobile"), true);
        $realdata = $jsonStr["data"];
        $uid = json_decode(get_content("https://udblgn.huya.com/web/anonymousLogin", "uid"), true)["data"]["uid"];

        function aes_decrypt($ciphertext, $key, $iv)
        {
            return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
        }

        $key = "abcdefghijklmnopqrstuvwxyz123456";
        $iv = "1234567890123456";
        $mediaurl = aes_decrypt("fIuPMpBI1RpRnM2JhbYHzvwCvwhHBF7Q+8k14m9h3N5ZfubHcDCEk08TnLwHoMI/SG7bxpqT6Rh+gZunSpYHf1JM/RmEC/S1SjRYWw6rwc3gGo3Rrsl3sojPujI2aZsb", $key, $iv);


        function get_uuid()
        {
            $now = intval(microtime(true) * 1000);
            $rand = rand(0, 1000) | 0;

            return intval(($now % 10000000000 * 1000 + $rand) % 4294967295);
        }

        function process_anticode($anticode, $uid, $streamname)
        {
            parse_str($anticode, $q);
            $q["t"] = '102';
            $q["ctype"] = 'tars_mp';
            $q["wsTime"] = dechex(time() + 21600);
            $q["ver"] = "1";
            $q["sv"] = date('YmdH');
            $q["seqid"] = strval(intval($uid) + intval(microtime(true) * 1000));
            $q["uid"] = strval($uid);
            $q["uuid"] = strval(get_uuid());
            $ss = md5("{$q["seqid"]}|{$q["ctype"]}|{$q["t"]}");
            $q["fm"] = base64_decode($q["fm"]);
            $q["fm"] = str_replace([
                "$0",
                "$1",
                "$2",
                "$3"
            ], [
                $q["uid"],
                $streamname,
                $ss,
                $q["wsTime"]
            ], $q["fm"]);
            $q["wsSecret"] = md5($q["fm"]);
            unset($q["fm"]);
            if (array_key_exists("txyp", $q))
            {
                unset($q["txyp"]);
            }

            return http_build_query($q);
        }

        function format($realdata, $uid)
        {
            $stream_info = [
                'flv' => [],
                'hls' => []
            ];
            $cdn_type = [
                'HY' => 'hycdn',
                'TX' => 'txcdn',
                'HW' => 'hwcdn',
                'HS' => 'hscdn',
                'WS' => 'wscdn',
                'AL' => 'hycdn'
            ];
            foreach ($realdata["stream"]["baseSteamInfoList"] as $s)
            {
                // var_dump($s);
                if ($s["sFlvUrl"])
                {
                    $stream_info["flv"][$cdn_type[$s["sCdnType"]]] = $s["sFlvUrl"] . '/' . $s["sStreamName"] . '.'
                        . $s["sFlvUrlSuffix"] . '?' . process_anticode($s["sFlvAntiCode"], $uid, $s["sStreamName"]);
                }
                if ($s["sHlsUrl"])
                {
                    $stream_info["hls"][$cdn_type[$s["sCdnType"]]] = $s["sHlsUrl"] . '/' . $s["sStreamName"] . '.'
                        . $s["sHlsUrlSuffix"] . '?' . process_anticode($s["sHlsAntiCode"], $uid, $s["sStreamName"]);
                }
            }

            return $stream_info;
        }

        if ($jsonStr["status"] == 200)
        {
            $realurl = format($realdata, $uid);
            if ($type == "display")
            {
                print_r($realurl);
                exit();
            }
            if ($media == "flv")
            {
                switch ($cdn)
                {
                    case $cdn:
                        $mediaurl = str_replace("http://", "https://", $realurl["flv"][$cdn]);
                        break;
                    default:
                        $mediaurl = str_replace("http://", "https://", $realurl["flv"]["hwcdn"]);
                        break;
                }
            }
            if ($media == "hls")
            {
                switch ($cdn)
                {
                    case $cdn:
                        $mediaurl = str_replace("http://", "https://", $realurl["hls"][$cdn]);
                        break;
                    default:
                        $mediaurl = str_replace("http://", "https://", $realurl["hls"]["hwcdn"]);
                        break;
                }
            }
            //header('location:' . $mediaurl);
            echo($mediaurl);
            // exit();
        }
        else
        {
            //header('location:' . $mediaurl);
            echo($mediaurl);
            // exit();
        }
        $firstUrl = $mediaurl;
        echo("<script>(async () => {
  try {
    //const firstUrl = 'https://example.com'; // 这里使用了一个示例URL
    ////await navigator.clipboard.writeText('$firstUrl');
    // console.log('复制成功:', firstUrl);
  } catch (err) {
    console.error('复制失败:', err.name, err.message);
  }
})();</script>");
    ?>

</div>
<!-- jQuery -->
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/??jquery/3.5.1/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-10-y/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/jeditable.js/2.0.19/jquery.jeditable.min.js"></script>
<!-- DataTables -->
<script src="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-10-y/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-10-y/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<!-- 引入 ECharts 文件 -->
<script src="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-10-y/echarts/4.8.0/echarts.min.js"></script>
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.4.0/dist/js/bootstrap-switch.min.js"></script>-->

<script src="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-10-y/tarekraafat-autocomplete.js/10.2.6/autoComplete.min.js"></script>

<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/hls.js/1.1.5/hls.min.js"></script>
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/flv.js/1.6.2/flv.min.js"></script>
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/dplayer/1.26.0/DPlayer.min.js"></script>
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

    let newdata = ["859042<br>正恒-紫宸【相声木兰】",
        "330679<br>怀逝【李白导师】",
        "391946<br>小炎【妲己的神】",
        "691346<br>宇晨【马可导师】",
        "825912<br>念青【嘴强王者】"];

    console.log(newdata)


    const autoCompleteJS = new autoComplete({
        selector: "#bid",
        placeHolder: "",
        threshold: 0,

        data: {
            src: newdata,
            cache: false,
        },
        resultsList: {
            element: (list, data) => {
                if (!data.results.length) {
                    // Create "No Results" message element
                    const message = document.createElement("div");
                    // Add class to the created element
                    message.setAttribute("class", "no_result");
                    // Add message text content
                    message.innerHTML = `<span>Found No Results for "${data.query}"</span>`;
                    // Append message element to the results list
                    list.prepend(message);
                }
            },
            noResults: true,
            maxResults: undefined,
        },
        resultItem: {
            highlight: true
        },
        events: {
            input: {
                selection: (event) => {
                    const selection = event.detail.selection.value;
                    autoCompleteJS.input.value = selection.split('<br>')[0];
                },
                focus: (event) => {
                    // console.log("Input Field in focus!");
                    // const inputValue = autoCompleteJS.input.value;
                    //
                    // if (inputValue.length) autoCompleteJS.start();
                    autoCompleteJS.start();
                }
            }
        }
    });

    // 定义显示消息的函数
    function showMessage(message, duration = 3000) {
        const messageBox = $("#message-box");
        messageBox.text(message).fadeIn();

        setTimeout(function () {
            messageBox.fadeOut();
        }, duration);
    }

    $(document).ready(function ()
    {
        function sendRequest(action, data, callback) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'pgsql.php', true);  // 确保文件名正确
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        try {
                            const jsonResponse = JSON.parse(xhr.responseText); // 尝试解析 JSON
                            callback(jsonResponse);
                        } catch (e) {
                            console.error("Failed to parse JSON response: ", e);
                            // showMessage("Error: Invalid response format.");
                        }
                    } else {
                        console.error("Request failed: ", xhr.status);
                        // showMessage("Error: " + xhr.statusText);
                    }
                }
            };
            xhr.send(data);
            //xhr.send(`action=${encodeURIComponent(action)}&${data}`);
        }

        // 读取现有的房间数据
        let roomData = JSON.parse(localStorage.getItem('roomData')) || {};

        function addOption()
        {
            // 遍历 roomData 并生成 <option> 元素
            let optionsHtml = '';
            let autoCompleteData = '';
            for (let roomNumber in roomData)
            {
                if (roomData.hasOwnProperty(roomNumber))
                {
                    optionsHtml += `<option value="${roomNumber}">${roomData[roomNumber]}</option>`;
                    // autoCompleteData += `${roomNumber}<br>${roomData[roomNumber]}`
                    newdata.push(`${roomNumber}<br>${roomData[roomNumber]}`);
                };
            }
            // $("#appNamelist").append(optionsHtml);
            // newdata = [...newdata, ...autoCompleteData];
            // newdata.push(autoCompleteData);
            autoCompleteJS.data.src = newdata;
        }
        addOption();

        function getRooms() {
            sendRequest('get', 'action=get', function(response) {
                const rooms = response.data;
                console.log(rooms)
                const roomList = rooms.map(room => `<option value="${room.room_id}">${room.room_name}</option>`).join('');
                const autoCompleteData = rooms.map(room => `${room.room_id}<br>${room.room_name}`);
                console.log(autoCompleteData)
                newdata = [...newdata, ...autoCompleteData];
                // newdata.push(autoCompleteData);
                autoCompleteJS.data.src = newdata;
                // $("#appNamelist").append(roomList);
            });
        }
        getRooms();


        $("#bid").focus();
        $("#bid").keydown(function (e)
        {
            if (e.keyCode == 13)
            {
                $('#btnConfirm').trigger("click");
            }
        });

        $("#btnConfirm").click(function ()
        {
            console.log('$("#bid").val()');
            console.log($("#bid").val());
            window.location.replace(window.location.protocol + "//" + window.location.host + window.location.pathname + "?id=" + $("#bid").val())
            //alert(window.location.href + "?id=" + $("#bid").val());
            //alert(window.location.host);
        });

        //保存数据
        $("#btnSubmit").click(function ()
        {
            console.log($("#roomId").val());
            console.log($("#roomName").val());

            // 添加新的房间号-房间名对
            roomData[$("#roomId").val()] = $("#roomName").val();

            // 将更新后的对象存储回 LocalStorage
            localStorage.setItem('roomData', JSON.stringify(roomData));

            // 输出更新后的数据
            console.log(JSON.parse(localStorage.getItem('roomData')));
            // 结果可能是：{101: 'Conference Room A', 102: 'Updated Conference Room B', 201: 'Meeting Room 1', 301: 'New Conference Room'}

            let tempOptionsHtml = `<option value="${$("#roomId").val()}">${$("#roomName").val()}</option>`;
            // $("#appNamelist").append(tempOptionsHtml);
            newdata.push(`${$("#roomId").val()}<br>${$("#roomName").val()}`);
            autoCompleteJS.data.src = newdata;

            $("#roomId").val('');
            $("#roomName").val('');
        });


        function addRoom() {
            const roomId = document.getElementById('room_id').value;
            const roomName = document.getElementById('room_name').value;
            const data = `action=add&room_id=${encodeURIComponent(roomId)}&room_name=${encodeURIComponent(roomName)}`;

            sendRequest('add', data, function(response) {
                // $("#appNamelist").append(`<option value="${roomId}">${roomName}</option>`);
                newdata.push(`${roomId}<br>${roomName}`);
                // 使用自定义的消息提示框
                showMessage(response.message || "房间信息已保存！");
                //showMessage(response.message);
                //getRooms(); // Refresh room list
                document.getElementById('room_id').value = '';
                document.getElementById('room_name').value = '';
            });
        }

        // 为保存按钮绑定点击事件
        $("#save_button").on("click", addRoom);

    });
</script>
</body>
</html>
