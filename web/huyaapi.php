<?php
header('Content-Type: application/json');
date_default_timezone_set("Asia/Shanghai");

$type = empty($_GET['type']) ? "nodisplay" : trim($_GET['type']);
$url = empty($_GET['id']) ? "391946" : trim($_GET['id']);
// 检查URL中是否包含数字
if (preg_match('/\d+/', $url, $match)) {
    $id = $match[0];
} else {
    $id = $url;
}
$cdn = empty($_GET['cdn']) ? "hscdn" : trim($_GET['cdn']);
$media = empty($_GET['media']) ? "flv" : trim($_GET['media']);
$roomurl = "https://mp.huya.com/cache.php?m=Live&do=profileRoom&roomid=" . $id;

function get_content($apiurl, $flag) {
    if ($flag == "mobile") {
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.3 Mobile/15E148 Safari/604.1'
        );
    } else {
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
    if ($flag == "uid") {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function aes_decrypt($ciphertext, $key, $iv) {
    return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
}

$key = "abcdefghijklmnopqrstuvwxyz123456";
$iv = "1234567890123456";
$mediaurl = aes_decrypt("fIuPMpBI1RpRnM2JhbYHzvwCvwhHBF7Q+8k14m9h3N5ZfubHcDCEk08TnLwHoMI/SG7bxpqT6Rh+gZunSpYHf1JM/RmEC/S1SjRYWw6rwc3gGo3Rrsl3sojPujI2aZsb", $key, $iv);

function get_uuid() {
    $now = intval(microtime(true) * 1000);
    $rand = rand(0, 1000) | 0;
    return intval(($now % 10000000000 * 1000 + $rand) % 4294967295);
}

function process_anticode($anticode, $uid, $streamname) {
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
    
    if (isset($q["fm"]) && !empty($q["fm"])) {
        $fm_decoded = base64_decode($q["fm"]);
        if ($fm_decoded !== false) {
            $q["fm"] = $fm_decoded;
            $q["fm"] = str_replace(["$0", "$1", "$2", "$3"], [$q["uid"], $streamname, $ss, $q["wsTime"]], $q["fm"]);
            $q["wsSecret"] = md5($q["fm"]);
        } else {
            $q["wsSecret"] = md5("default|$uid|$streamname|$ss|{$q["wsTime"]}");
        }
    } else {
        $q["wsSecret"] = md5("default|$uid|$streamname|$ss|{$q["wsTime"]}");
    }
    unset($q["fm"]);
    if (array_key_exists("txyp", $q)) {
        unset($q["txyp"]);
    }
    return http_build_query($q);
}

function format($realdata, $uid) {
    $stream_info = ['flv' => [], 'hls' => []];
    $cdn_type = ['HY' => 'hycdn', 'TX' => 'txcdn', 'HW' => 'hwcdn', 'HS' => 'hscdn', 'WS' => 'wscdn', 'AL' => 'hycdn'];
    $unknown_cdn_types = []; // 记录未知的 CDN 类型

    foreach ($realdata["stream"]["baseSteamInfoList"] as $s) {
        if (!isset($cdn_type[$s["sCdnType"]])) {
            $unknown_cdn_types[] = $s["sCdnType"]; // 记录未知 CDN 类型
            $cdn_key = 'unknown_' . $s["sCdnType"];
        } else {
            $cdn_key = $cdn_type[$s["sCdnType"]];
        }
        if ($s["sFlvUrl"]) {
            $stream_info["flv"][$cdn_key] = $s["sFlvUrl"] . '/' . $s["sStreamName"] . '.' . $s["sFlvUrlSuffix"] . '?' . process_anticode($s["sFlvAntiCode"], $uid, $s["sStreamName"]);
        }
        if ($s["sHlsUrl"]) {
            $stream_info["hls"][$cdn_key] = $s["sHlsUrl"] . '/' . $s["sStreamName"] . '.' . $s["sHlsUrlSuffix"] . '?' . process_anticode($s["sHlsAntiCode"], $uid, $s["sStreamName"]);
        }
    }
    
    // 如果有未知 CDN 类型，在后续选择中触发错误并输出
    if (!empty($unknown_cdn_types)) {
        return ['stream_info' => $stream_info, 'unknown_cdn_types' => $unknown_cdn_types];
    }
    return $stream_info;
}

$jsonStr = json_decode(get_content($roomurl, "mobile"), true);
$realdata = $jsonStr["data"];
$uid = json_decode(get_content("https://udblgn.huya.com/web/anonymousLogin", "uid"), true)["data"]["uid"];

if ($jsonStr["status"] == 200) {
    $format_result = format($realdata, $uid);
    
    // 检查 format 返回值是否包含未知 CDN 类型
    if (is_array($format_result) && isset($format_result['unknown_cdn_types'])) {
        $realurl = $format_result['stream_info'];
        $unknown_cdn_types = $format_result['unknown_cdn_types'];
    } else {
        $realurl = $format_result;
        $unknown_cdn_types = [];
    }

    if ($type == "display") {
        echo json_encode($realurl);
        exit();
    }
    if ($media == "flv") {
        if (isset($realurl["flv"][$cdn])) {
            $mediaurl = str_replace("http://", "https://", $realurl["flv"][$cdn]);
        } elseif (isset($realurl["flv"]["hscdn"])) {
            $mediaurl = str_replace("http://", "https://", $realurl["flv"]["hscdn"]);
        } elseif (isset($realurl["flv"]["txcdn"])) {
            $mediaurl = str_replace("http://", "https://", $realurl["flv"]["txcdn"]);
        } elseif (isset($realurl["flv"]["hycdn"])) {
            $mediaurl = str_replace("http://", "https://", $realurl["flv"]["hycdn"]);
        } else {
            $error_response = ['error' => 'No available FLV stream for any CDN', 'realdata' => $realdata];
            if (!empty($unknown_cdn_types)) {
                $error_response['unknown_cdn_types'] = $unknown_cdn_types;
            }
            echo json_encode($error_response);
            exit();
        }
    }
    if ($media == "hls") {
        if (isset($realurl["hls"][$cdn])) {
            $mediaurl = str_replace("http://", "https://", $realurl["hls"][$cdn]);
        } elseif (isset($realurl["hls"]["hscdn"])) {
            $mediaurl = str_replace("http://", "https://", $realurl["hls"]["hscdn"]);
        } elseif (isset($realurl["hls"]["txcdn"])) {
            $mediaurl = str_replace("http://", "https://", $realurl["hls"]["txcdn"]);
        } elseif (isset($realurl["hls"]["hycdn"])) {
            $mediaurl = str_replace("http://", "https://", $realurl["hls"]["hycdn"]);
        } else {
            $error_response = ['error' => 'No available HLS stream for any CDN', 'realdata' => $realdata];
            if (!empty($unknown_cdn_types)) {
                $error_response['unknown_cdn_types'] = $unknown_cdn_types;
            }
            echo json_encode($error_response);
            exit();
        }
    }
    echo json_encode(['url' => $mediaurl]);
} else {
    echo json_encode(['error' => 'Failed to fetch stream data from Huya API', 'realdata' => $realdata]);
}
?>