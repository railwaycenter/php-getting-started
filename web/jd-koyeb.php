<?php

    $header = array('User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
                    'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding:gzip, deflate, br',
                    'Accept-Language:zh-CN,zh;q=0.9',
                    'Host:item-soa.jd.com',
);

    $itemHeader = array('User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.109 Safari/537.36',
                    'Accept-Encoding:gzip, deflate, br',
                    'Host:item.jd.com');

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
        }
        catch (Exception $e)
        {
            //$this->_log($e->getMessage());
        }
        $curlError = curl_error($ch);
        if (!empty($curlError))
        {
            echo('curlError:'.$curlError);
        }
        curl_close($ch);
        return $content;
    }

function go()
{
    try
    {
        $str = getUrl("https://item-soa.jd.com/getWareBusiness?skuId=100041068457", $GLOBALS['header']);
        // var_dump($GLOBALS['header']);

        if (strpos($str, 'pdos_captcha'))
        {
            echo 'pdos_captcha,出错啦';
        }
        $str = json_decode($str, true);
        // var_dump($str);
        $price = $str['price']['op'];
        echo('$price:' . $price . "<br>");

        $item_name = $str['wareInfo']['wname'];
        echo('$item_name:' . $item_name . "<br>");


    }
    catch (Exception $e)
    {
    }


    echo 'ok';
}

go();
?>
