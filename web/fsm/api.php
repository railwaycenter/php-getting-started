<?php
    // 设置 API 的 URL
    $apiUrl = 'https://api.fsm.name/Torrents/listTorrents';

    // 获取请求中的 API Token
    $apiToken = isset($_SERVER['HTTP_APITOKEN']) ? $_SERVER['HTTP_APITOKEN'] : '';

    // 预定义的有效 API Token
    // $validTokens = ['QQT65EfxyZIAI0Sn3eFxPBWYNaovugk4']; // 可以根据需要从数据库读取

    // 验证 API Token 是否有效
    // if (!in_array($apiToken, $validTokens)) {
    //     header('HTTP/1.1 403 Forbidden');
    //     echo json_encode(['error' => '无效的 API Token']);
    //     exit();
    // }

     // 新增部分：POST请求，用于根据 action 进行不同的操作
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 获取请求体中的 JSON 数据
        $requestBody = file_get_contents('php://input');
        $data = json_decode($requestBody, true);

        // 获取传入的 action 和 torrent_id
        $action = isset($data['action']) ? $data['action'] : '';

        // 处理不同的 action 请求
        switch ($action) {
            case 'getInfo':

                // 验证请求参数
                if (!isset($data['page']) || !isset($data['pageSize'])) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['error' => '缺少必要的请求参数']);
                    exit();
                }

                // 对输入参数进行简单的过滤和验证
                $page = filter_var($data['page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $pageSize = filter_var($data['pageSize'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $keyword = isset($data['keyword']) ? htmlspecialchars($data['keyword'], ENT_QUOTES, 'UTF-8') : '';

                // 如果参数无效，返回错误
                if (!$page || !$pageSize) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['error' => '无效的分页参数']);
                    exit();
                }

                // 设置请求体数据
                $params = [
                    'type' => isset($data['type']) ? $data['type'] : '0',
                    'systematics' => '0',
                    'tags' => '[]',
                    'keyword' => $keyword,
                    'page' => $page,
                    'pageSize' => $pageSize
                ];
                // 构建查询字符串
                $queryString = http_build_query($params);

                // 将查询字符串添加到 API URL 后面
                $apiUrl .= '?' . $queryString;

                // 初始化 cURL 会话
                $ch = curl_init();

                // 设置 cURL 参数
                curl_setopt($ch, CURLOPT_URL, $apiUrl);  // 设置目标 API 地址
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 设置返回结果作为字符串而不是直接输出
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'APITOKEN: ' . $apiToken,
                    'Content-Type: application/json',
                ]);

                // 禁用 SSL 验证
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                // 设置 cURL 请求的 JSON 数据
                // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

                // 执行 cURL 请求
                $response = curl_exec($ch);

                // 检查是否发生错误
                if (curl_errno($ch)) {
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['error' => 'cURL 错误: ' . curl_error($ch)]);
                    exit();
                }

                // 关闭 cURL 会话
                curl_close($ch);

                // 解析返回的数据
                $data = json_decode($response, true);

                // 如果外部 API 返回数据不合法，处理错误
                if (!$data || !isset($data['data'])) {
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['error' => '无法获取有效的种子数据']);
                    exit();
                }

                // var_dump($data['page']);
                // 输出数据（你可以选择根据需要进行数据处理）
                echo json_encode($data);
                break;

            case 'addListMySelfRss':
                $torrentId = isset($data['torrent_id']) ? $data['torrent_id'] : '';
                if ($torrentId) {
                    // 调用通用 cURL 请求函数，处理加入个人 RSS 的操作
                    $rssApiUrl = 'https://api.fsm.name/Torrents/addListMySelfRss';  // 你的目标 API 地址
                    $postData = json_encode(['tids' => $torrentId]);
                    $rssResponse = sendCurlRequest($rssApiUrl, $postData);
                    echo json_encode(json_decode($rssResponse, true)); // 返回 API 响应
                }
                break;

            case 'addCart':
                $torrentId = isset($data['torrent_id']) ? $data['torrent_id'] : '';
                if ($torrentId) {
                    // 调用通用 cURL 请求函数，处理加入购物车的操作
                    $cartApiUrl = 'https://api.fsm.name/Torrents/addCart';  // 你的目标 API 地址
                    $postData = json_encode(['torrent_id' => $torrentId]);
                    $cartResponse = sendCurlRequest($cartApiUrl, $postData);
                    echo json_encode($cartResponse); // 返回 API 响应
                }
                break;

            default:
                echo json_encode(['success' => false, 'msg' => '1234567未知操作！']);
                break;
        }
    }


    /**
     * 通用的 cURL 请求函数
     *
     * @param string $url API 请求的 URL
     * @param string $postData 发送的 POST 数据
     * @return string 返回 API 响应
     */
    function sendCurlRequest($url, $postData) {
        global $apiToken; // 引入全局变量 apiToken

        // 初始化 cURL 会话
        $ch = curl_init();

        // 设置 cURL 参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 设置返回结果作为字符串
        curl_setopt($ch, CURLOPT_POST, true);  // 使用 POST 请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  // 传递 POST 数据
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',  // 设置 Content-Type
            'APITOKEN: ' . $apiToken,  // 固定 API Token
        ]);

        // 禁用 SSL 验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // 执行 cURL 请求
        $response = curl_exec($ch);

        // 检查是否发生错误
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }

        // 关闭 cURL 会话
        curl_close($ch);

        return $response; // 返回 API 响应
    }
?>
