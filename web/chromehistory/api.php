<?php
// 设置返回内容类型为 JSON
header("Content-Type: application/json");
// 设置响应头，允许跨域请求（可选）
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// 版本号 v1.0.8
const VERSION = 'v1.0.8';

// 引入 Medoo 单文件
require_once 'Medoo.php';

use Medoo\Medoo;

// 引入配置文件并赋值
$config = require 'config.php';

// 初始化数据库连接
$database = new Medoo($config);

$hash_token = getenv('hash_token');
$api_token = $_GET['api_token'] ?? '';
if(!password_verify($api_token, $hash_token)) {
    sendResponse(401, ['message' => '未授权：需要会话或 API 密钥']);
}

// 辅助函数：清理字符串，防止 XSS
function sanitizeString($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

// 辅助函数：返回 JSON 响应
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    // 添加版本号到响应中
    $data['version'] = VERSION;
    echo json_encode($data);
    exit;
}

// 验证字段函数
function validateFields($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            return "缺少字段: $field";
        }
    }

    $url = sanitizeString($data['url']);
    $title = sanitizeString($data['title']);
    $date = (int)$data['date'];
    $isBookmarked = $data['isBookmarked'];

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return 'URL 无效';
    }
    if (!is_string($title) || empty(trim($title))) {
        return '标题必须是非空字符串';
    }
    if (!is_int($date) || $date <= 0) {
        return '日期必须是正整数时间戳';
    }
    // 验证 isBookmarked 为 true 或 false
    if (!is_bool($isBookmarked)) {
        return 'isBookmarked 必须是 true 或 false';
    }

    return ['url' => $url, 'title' => $title, 'date' => $date, 'isBookmarked' => $isBookmarked];
}

// 检查标题或 URL 是否包含黑名单词
function checkBlacklist($database, $title, $url) {
    $blacklist = $database->get('blacklist', 'words', ['id' => 1]); // 假设 ID 为 1 的记录
    if (!$blacklist) return false;
    $words = array_filter(array_map('trim', explode(',', $blacklist)));
    foreach ($words as $word) {
        if (stripos($title, $word) !== false) {
            return "标题中含有黑名单词: " . $word;
        }
        if (stripos($url, $word) !== false) {
            return "URL 中含有黑名单词: " . $word;
        }
    }
    return false;
}

// 插入或更新书签逻辑
function insertOrUpdateBookmark($database, $validatedData) {
    $blacklistCheck = checkBlacklist($database, $validatedData['title'], $validatedData['url']);
    if ($blacklistCheck) {
        return ['message' => $blacklistCheck];
    }

    $existing = $database->select('bookmarks', ['id', 'date', 'created_at'], [
        'url' => $validatedData['url'],
        'deleted_at' => null,
        'ORDER' => ['created_at' => 'DESC']
    ]);
    
    // 将时间戳转换为 Y-m-d H:i:s 格式
    $validatedDateFormatted = date('Y-m-d H:i:s', $validatedData['date'] / 1000);

    if (!empty($existing)) {
        $existingDate = $existing[0]['date'];
        $timeDifference = abs(strtotime($validatedDateFormatted) - strtotime($existingDate));
        if ($timeDifference < 86400) { // 修改为 24 小时 (86400 秒)
            $database->update('bookmarks', ['date' => $validatedDateFormatted], ['id' => $existing[0]['id']]);
            return ['message' => '记录已更新', 'id' => $existing[0]['id'], 'isBookmarked' => $validatedData['isBookmarked']];
        }
    }

    $database->insert('bookmarks', [
        'url' => $validatedData['url'],
        'title' => $validatedData['title'],
        'date' => $validatedDateFormatted,
        'isBookmarked' => $validatedData['isBookmarked'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    return ['message' => '数据入库成功', 'id' => $database->id(), 'isBookmarked' => $validatedData['isBookmarked']];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        if ($action === 'get_blacklist') {
            // 获取黑名单（单条记录）
            $blacklist = $database->get('blacklist', ['id', 'words'], ['id' => 1]);
            if (!$blacklist) {
                $database->insert('blacklist', ['words' => '']);
                $blacklist = $database->get('blacklist', ['id', 'words'], ['id' => 1]);
            }
            sendResponse(200, $blacklist);
        } else {
            // 处理书签数据获取请求
            $itemsPerPageOptions = [5, 10, 20, 30, 50, 100]; // 更新选项以支持前端的 30、50、100
            $itemsPerPage = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $itemsPerPageOptions) ? (int)$_GET['per_page'] : 30; // 默认值改为 30
            $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            // 限制 search 参数长度（防止过长查询）
            if (strlen($search) > 100) {
                sendResponse(400, ['message' => '搜索关键词过长，最大 100 个字符']);
            }
            // 限制分页范围（防止过大偏移）
            if ($page > 1000) {
                sendResponse(400, ['message' => '页码超出允许范围']);
            }

            $offset = ($page - 1) * $itemsPerPage;

            // 构建查询条件
            $conditions = ['deleted_at' => null];
            if ($search) {
                $conditions['OR'] = [
                    'url[~]' => "%$search%",
                    'title[~]' => "%$search%"
                ];
            }

            // 建议为 url、title 和 deleted_at 字段添加索引以优化查询性能
            $totalItems = $database->count('bookmarks', $conditions);
            $totalPages = ceil($totalItems / $itemsPerPage);

            $bookmarks = $database->select('bookmarks', [
                'id',
                'url',
                'title',
                'date',
                'isBookmarked',
                'created_at'
            ], array_merge($conditions, [
                'LIMIT' => [$offset, $itemsPerPage],
                'ORDER' => ['id' => 'DESC']
            ]));
            
            // 将 isBookmarked 从 0/1 转换为 true/false
            foreach ($bookmarks as &$bookmark) {
                $bookmark['isBookmarked'] = (bool)$bookmark['isBookmarked'];
            }
            unset($bookmark); // 释放引用

            sendResponse(200, [
                'bookmarks' => $bookmarks,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage
            ]);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 处理 CRUD 请求
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            sendResponse(400, ['message' => '无效的 JSON 数据']);
        }

        $action = $data['action'] ?? '';
        $requiredFields = ['url', 'title', 'date', 'isBookmarked'];

        if (empty($action) || $action === 'add') {
            $validation = validateFields($data, $requiredFields);
            if (is_string($validation)) {
                sendResponse(400, ['message' => $validation]);
            }
            $validatedData = $validation;

            $result = insertOrUpdateBookmark($database, $validatedData);
            if (isset($result['message']) && strpos($result['message'], '黑名单') !== false) {
                sendResponse(400, $result);
            }
            sendResponse(200, $result);
        } else {
            if (!in_array($action, ['edit', 'delete', 'batch_delete', 'update_blacklist'])) {
                sendResponse(400, ['message' => '无效的操作']);
            }

            $actionFields = [
                'edit' => array_merge(['id'], $requiredFields),
                'delete' => ['id'],
                'batch_delete' => ['ids'],
                'update_blacklist' => ['words']
            ];

            foreach ($actionFields[$action] as $field) {
                if (!isset($data[$field])) {
                    sendResponse(400, ['message' => "缺少字段: $field"]);
                }
            }

            if ($action === 'edit') {
                $validation = validateFields($data, $requiredFields);
                if (is_string($validation)) {
                    sendResponse(400, ['message' => $validation]);
                }
                $validatedData = $validation;
                $id = (int)$data['id'];

                $blacklistCheck = checkBlacklist($database, $validatedData['title'], $validatedData['url']);
                if ($blacklistCheck) {
                    sendResponse(400, ['message' => $blacklistCheck]);
                }

                $database->update('bookmarks', [
                    'url' => $validatedData['url'],
                    'title' => $validatedData['title'],
                    'date' => $validatedData['date'],
                    'isBookmarked' => $validatedData['isBookmarked']
                ], ['id' => $id]);
                sendResponse(200, ['message' => '记录已更新', 'id' => $id, 'isBookmarked' => $validatedData['isBookmarked']]);
            } elseif ($action === 'delete') {
                $id = (int)$data['id'];
                $database->update('bookmarks', ['deleted_at' => date('Y-m-d H:i:s')], ['id' => $id]);
                $isBookmarked = $database->get('bookmarks', 'isBookmarked', ['id' => $id]);
                sendResponse(200, ['message' => '记录已软删除', 'id' => $id, 'isBookmarked' => (bool)$isBookmarked]);
            } elseif ($action === 'batch_delete') {
                if (!is_array($data['ids']) || empty($data['ids'])) {
                    sendResponse(400, ['message' => '缺少有效的 ID 数组']);
                }
                $ids = array_map('intval', $data['ids']); // 确保 ID 为整数
                $database->update('bookmarks', ['deleted_at' => date('Y-m-d H:i:s')], ['id' => $ids]);
                sendResponse(200, ['message' => '批量软删除成功', 'count' => count($ids)]);
            } elseif ($action === 'update_blacklist') {
                $words = sanitizeString($data['words']);
                $existing = $database->get('blacklist', 'id', ['id' => 1]);
                if ($existing) {
                    $database->update('blacklist', ['words' => $words], ['id' => 1]);
                } else {
                    $database->insert('blacklist', ['id' => 1, 'words' => $words]);
                }
                sendResponse(200, ['message' => '黑名单已更新']);
            }
        }
    } else {
        sendResponse(405, ['message' => '不支持的请求方法']);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendResponse(500, ['message' => '数据库错误: ' . $e->getMessage()]);
}
?>