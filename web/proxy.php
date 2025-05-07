<?php
header('Content-Type: application/json');

/**
 * 代理请求函数（虎牙 API）
 * @param string $id 虎牙 ID
 * @return string JSON 格式的响应
 */
function proxyA($id) {
    if (empty($id)) {
        return json_encode(['error' => '缺少 ID 参数']);
    }

    // 目标 API URL
    $apiUrl = "https://www.goodiptv.club/huya/{$id}?type=json";

    // 初始化 cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // 禁用 SSL 验证（仅用于开发环境）
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 执行请求
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // 检查请求是否成功
    if ($response === false || $httpCode !== 200) {
        $error = curl_error($ch) ?: '无法连接到目标 API';
        $result = json_encode(['error' => $error, 'status' => $httpCode]);
    } else {
        $result = $response;
    }

    // 关闭 cURL
    curl_close($ch);
    return $result;
}

/**
 * 代理 B 请求函数（示例）
 * @param string $id 输入 ID
 * @return string JSON 格式的响应
 */
function proxyB($id) {
    if (empty($id)) {
        return json_encode(['error' => '缺少 ID 参数']);
    }

    // 示例 API URL（替换为实际 API）
    $apiUrl = "https://api.example.com/b/{$id}";

    // 初始化 cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 执行请求
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // 检查请求是否成功
    if ($response === false || $httpCode !== 200) {
        $error = curl_error($ch) ?: '无法连接到 B API';
        $result = json_encode(['error' => $error, 'status' => $httpCode]);
    } else {
        $result = $response;
    }

    // 关闭 cURL
    curl_close($ch);
    return $result;
}

/**
 * 代理 C 请求函数（示例）
 * @param string $id 输入 ID
 * @return string JSON 格式的响应
 */
function proxyC($id) {
    if (empty($id)) {
        return json_encode(['error' => '缺少 ID 参数']);
    }

    // 示例 API URL（替换为实际 API）
    $apiUrl = "https://api.example.com/c/{$id}";

    // 初始化 cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 执行请求
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // 检查请求是否成功
    if ($response === false || $httpCode !== 200) {
        $error = curl_error($ch) ?: '无法连接到 C API';
        $result = json_encode(['error' => $error, 'status' => $httpCode]);
    } else {
        $result = $response;
    }

    // 关闭 cURL
    curl_close($ch);
    return $result;
}

// 主逻辑：请求分发（数组映射）
$actionMap = [
    'proxy' => 'proxyA',
    'proxyb' => 'proxyB',
    'proxyc' => 'proxyC'
];

$action = isset($_GET['action']) ? strtolower(trim($_GET['action'])) : 'proxy';
$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (isset($actionMap[$action]) && function_exists($actionMap[$action])) {
    echo call_user_func($actionMap[$action], $id);
} else {
    echo json_encode(['error' => '无效的 action 参数']);
}
?>