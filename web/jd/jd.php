<?php
    require 'config.php';

    require 'Logger.php';
    $logHandler = new CLogFileHandler(__DIR__ . '/logs/phpjd' . date('Y-m-d') . '.log');

    $log = Log::Init($logHandler, 15);

    $header = array('User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
                    'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding:gzip, deflate, br',
                    'Accept-Language:zh-CN,zh;q=0.9',
                    'Host:item-soa.jd.com',
);

    $itemHeader = array('User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.109 Safari/537.36',
                    'Accept-Encoding:gzip, deflate, br',
                    'Host:item.jd.com');
//    $header = {
//    'User-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36',
//        'Cookie': Cookie,
//        'Connection': 'keep-alive',
//        'Accept': '*/*',
//        'Accept-Encoding': 'gzip, deflate, sdch',
//        'Accept-Language': 'zh-CN,zh;q=0.8',
//        'Host': 'p.3.cn',
//        'Referer': 'https://book.jd.com/booktop/0-0-0.html?category=1713-0-0-0-10001-1'
//    }

    $headerm = array('Host:item.m.jd.com',
                     'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
                     'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                     'Accept-Encoding:gzip, deflate, br');


    function getUrl($url, $header = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回数据不直接输出
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
       // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
       // curl_setopt($ch,CURLOPT_COOKIEFILE, $this->lastCookieFile); //使用提交后得到的cookie数据
        try
        {
            $content = curl_exec($ch); //执行并存储结果

            //获取请求返回码，请求成功返回200
            $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
            // echo 'code:'.$code . "<br>";

            //获取一个cURL连接资源句柄的信息。
            //$headers 中包含跳转的url路径
            $headers = curl_getinfo($ch);
            // var_dump($headers);
            // echo ('content:'.$content);
        }
        catch (Exception $e)
        {
            //$this->_log($e->getMessage());
        }
        $curlError = curl_error($ch);
        if (!empty($curlError))
        {
            //$this->_log($curlError);
            echo('curlError:'.$curlError);
        }
        curl_close($ch);
        //$output = json_decode($content,true);
        //return $output;
        return $content;
    }


    function getItemName($url)
    {
        //echo $url;
        $str = getUrl($url, $GLOBALS['itemHeader']);
        //echo $str;
        preg_match("/title>(.*?)<\/title/", $str, $pat_array);
        //print_r($pat_array[1]);
        //$item_name = str_ireplace("【行情 报价 价格 评测】-京东", "", $pat_array[1]);
        $item_name = preg_replace("/【(.*?)】/", "", $pat_array[1]);
        $item_name = preg_replace("/-京东/", "", $item_name);

        return $item_name;
    }

    function sendmessageToTG($sendText)
    {
        $sendText = urlencode($sendText);
        $url = "https://xxx.com/tg.php?chatid=-100&text={$sendText}";
        try
        {
            echo getUrl($url);
        }
        catch (Exception $e)
        {
            echo 'sendmessageToTG异常: ' . $e->getMessage();
        }

        //echo 'totg';
    }

    //$database->update("monitor",["mall_name"=>"jd"],["id"=>1]);
    //echo(getItemName("https://item.jd.com/5273061.html"));

    $currentpage  = intval($database->get("monitor_config", "value", ["key" => "currentpage"]));
    $perpagecount = intval($database->get("monitor_config", "value", ["key" => "perpagecount"]));

    $user_send_message = intval($database->get("monitor_config", "value", ["key" => "user_send_message"]));

    $allpages     = ceil($database->count("monitor", ["status" => 1,
                                                      "mall_name" => "京东"]) / $perpagecount);
    $pageStart    = ($currentpage - 1) * $perpagecount;
    $datas        = $database->select("monitor", "*", ["status" => 1,
                                                       "mall_name" => "京东",
                                                       "ORDER" => ["id" => "DESC"],
                                                       'LIMIT' => [$pageStart,
                                                                   // 1,
                                                                   // 1]]);
                                                                     $perpagecount]]);
    //$datas = $database->select("monitor", "*", ["status" => 1,"mall_name" =>"京东", "ORDER" => ["id" => "DESC"]]);
    //    echo '$allpages:',$allpages,  "<br>";
    //    echo '$currentpage:',($currentpage),  "<br>";
    //    echo '$perpagecount:',($perpagecount),  "<br>";
    // var_dump($datas);

    foreach ($datas as $data)
    {
        try
        {
//            echo "dataid:", $data["id"],  "<br>";
            Log::DEBUG('ID：' . $data["id"]);
            $item_url = $data["item_url"];
            $today    = date('Y-m-d H:i:s');

            Log::DEBUG('数据：' . $item_url);
            //echo('$item_name：' . $data["item_name"]);
            //1.获取商品名称
            if (!$data["item_name"])
            {
                $item_name = getItemName($item_url);
                Log::DEBUG('$item_name：' . $item_name);
                $database->update("monitor", ["item_name" => $item_name], ["id" => $data["id"]]);
                $data["item_name"] = $item_name;
            }

            /* 20240607 start
            //2.获取商品id后，再获取价格
            preg_match("/[0-9]+/", $item_url, $pat_array);
            //echo ($pat_array[0]);
            // $str = getUrl("https://p.3.cn/prices/mgets?pin=&skuIds=J_" . $pat_array[0], $GLOBALS['header']);
//            $str = getUrl("https://p.3.cn/prices/get?type=1&area=1_72_2799&pdtk=&pdpin=&pdbp=0&skuid=J_" . $pat_array[0], $GLOBALS['header']);
//            echo('str:'.$str."<br>");
//             var_dump($GLOBALS['header']);
//             echo("pat_array:". $pat_array[0]."<br>");
            $str = getUrl("https://item-soa.jd.com/getWareBusiness?skuId=" . $pat_array[0], $GLOBALS['header']);
             Log::DEBUG('item-soa: ' . $str);
            if(strpos($str,'pdos_captcha'))
            {
                echo 'pdos_captcha,出错啦';
                Log::DEBUG($pat_array[0].':pdos_captcha,出错啦');
                continue;
            }
            $str   = json_decode($str,true);
            // var_dump($str);
            $price = $str['price']['p'];
            echo('$price:'.$price."<br>");

            $item_name=$str['wareInfo']['wname'];
            echo('$item_name:'.$item_name."<br>");

            Log::DEBUG('$price：' . $price.' $item_name: '.$item_name);

            if ($price === "-1.00")
            {
                continue;
            }
            //日期非当日
            if ($data['monitor_date'] != date('Y-m-d'))
            {
                $database->update("monitor", ['send_message_count' => 1,
                                              'monitor_date' => date('Y-m-d')], ["id" => $data["id"]]);
                echo ' monitor_date:', $data['monitor_date'] ,"<br> \n";
            }

            //3.比较价格
            if ($price <= $data["user_price"] && $price)
            {
                echo $data["item_name"], " price:", $price, "<br> \n";

                $database->update("monitor", ["item_price" => $price], ["id" => $data["id"]]);

                $sendText = "<a href='" . $data["item_url"] . "'>" . $data["item_name"] . "</a>\n预期价格：" . $data["user_price"] . "\n现价: <b>" . $price . "</b> \n";

                //比较发送次数
                // if ($data['send_message_count'] < $data['user_send_message'])
                if ($data['send_message_count'] <= $user_send_message)
                {
                    sendmessageToTG($sendText);
                    $database->update("monitor", ["user_price" => $price - 10], ["id" => $data["id"]]);
                    // $database->update("monitor", ['send_message_count[+]' => 1], ["id" => $data["id"]]);
                    echo 'sendmessage', "<br> \n";
                }
                else
                {
                    $database->update("monitor", ["user_price[-]" => 10], ["id" => $data["id"]]);
                    echo 'sendmessage user_price', "<br> \n";
                }

            }
            else
            {
                //echo $data["item_name"], " user_price:", $data["user_price"], "<br> \n";
                $database->update("monitor", ["item_price" => $price], ["id" => $data["id"]]);
            }

            //echo "id:", $data["id"];
            //4.插入或更新历史库
            $monitorData = $database->get("monitor_item_history", "*", ["monitor_id" => $data["id"],
                                                                        "monitor_date" => date('Y-m-d')]);
            // echo "monitorData:", $monitorData;
            // var_dump($monitorData);
            if ($monitorData)
            {
                if ($price < $monitorData["monitor_price"] && $price)
                {
                    $database->update("monitor_item_history", ["monitor_price" => $price], ["id" => $monitorData["id"]]);
                }
                elseif(!$monitorData["monitor_price"])
                {
                    $database->update("monitor_item_history", ["monitor_price" => $price], ["id" => $monitorData["id"]]);
                }

            }
            else
            {
                $database->insert("monitor_item_history", ["monitor_id" => $data["id"],
                                                           "monitor_date" => date('Y-m-d'),
                                                           "monitor_price" => $price]);
            }


            20240607 end */
            //echo "monitorData:  over";
            // xsleep(5);
        }
        catch (Exception $e)
        {
            Log::DEBUG('Foreach：' . $e->getMessage());
        }
    }


    $currentpage = $currentpage - 1;
    if ($currentpage <= 0)
    {
        $currentpage = $allpages;
    }
    $database->update("monitor_config", ["value" => $currentpage,"mtime" =>date('Y-m-d H:i:s')], ["key" => "currentpage"]);
    $database->update("monitor_config", ["value" => $allpages], ["key" => "allpages"]);
    echo 'ok';
?>