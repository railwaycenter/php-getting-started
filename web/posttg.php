<?php
    date_default_timezone_set('Asia/Shanghai');//'Asia/Shanghai'   亚洲/上海
    $api_key = 'CHANGE HERE';
    function send_post($postArray)
    {
        $params = $postArray;
        $ch     = curl_init("https://api.telegram.org/bot" . strval(getenv('api_id')) . "/sendMessage");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $result = curl_exec($ch);
        //捕抓异常
        if (curl_errno($ch))
        {
            $code = 400; //执行异常
            $data = curl_error($ch);
            curl_close($ch);

            return $data;
        }
        curl_close($ch);

        return $result;
    }

    $today    = date('Y-m-d H:i:s');
    $text     = @$_POST["text"];
    $tgid     = isset($_POST['chat_id']) ? $_POST['chat_id'] : strval(getenv('chat_id'));
    $parse_mode     = isset($_POST['parse_mode']) ? $_POST['parse_mode'] : 'HTML';
    //$sendText = urlencode($today . "\n" . $text);
    $sendText = $text;
    if ($text)
    {
        $params = ['chat_id' => $tgid,
                   'text' => $sendText,
                   'parse_mode' => $parse_mode];
        echo send_post($params);
    }
    else
    {
        echo "hello";
    }
?>
