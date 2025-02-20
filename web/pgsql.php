<?php
    error_reporting(0); // 禁用错误报告
    ob_start(); // 开始输出缓冲
    header('Content-Type: application/json'); // 设置返回类型为 JSON
    $host = getenv('DB_HOST');
    $port = '5432';
    $dbname = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    try {
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode([message => "Connection failed: " . $e->getMessage()]);
        exit;
    }

    // 创建表的函数
    function createTable($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS roomData (
                id SERIAL PRIMARY KEY,
                room_id VARCHAR(50),
                room_name VARCHAR(100),
                add_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        $pdo->exec($sql);
        return ["message" => "Table 'roomData' created successfully (if it did not already exist)."];
    }

    // 删除表的函数
    function dropTable($pdo) {
        $sql = "DROP TABLE IF EXISTS roomData";
        $pdo->exec($sql);
        return ["message" => "Table 'roomData' deleted successfully."];
    }

    // 增加数据的函数
    function addData($pdo, $room_id, $room_name) {
        // 简单的数据类型检查示例，这里可以根据实际需求进一步完善
        if (!is_string($room_id) ||!is_string($room_name)) {
            return [message => "room_id and room_name should be strings"];
        }

        // 提取 room_id 中的数字部分（无论它是纯数字还是 URL）
        preg_match('/\d+/', $room_id, $matches);
        $numeric_room_id = isset($matches[0]) ? $matches[0] : null; // 如果没有匹配到数字，则为 null

        // 检查是否存在重复的 room_id 或 room_name
        $checkSql = "SELECT COUNT(*) FROM roomData WHERE room_name = :room_name OR room_id = :room_id OR room_id = :numeric_room_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([
            'room_id' => $room_id,
            'room_name' => $room_name,
            'numeric_room_id' => $numeric_room_id
        ]);
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            return ["message" => "Duplicate room_id or room_name found. Room addition aborted."];
        }

        // 插入新记录
        $sql = "INSERT INTO roomData (room_id, room_name) VALUES (:room_id, :room_name)";
        $stmt = $pdo->prepare($sql);
        try
        {
            $stmt->execute([
                'room_id' => $room_id,
                'room_name' => $room_name
            ]);
            return ["message" => "Room added successfully."];
        }catch (PDOException $e) {
            return [message => "Room addition failed: ". $e->getMessage()];
        }
    }

    // 获取数据的函数
    function getData($pdo) {
        $sql = "SELECT * FROM roomData order by id asc";
        $stmt = $pdo->query($sql);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ["data" => $rooms]; // 返回 JSON 格式的数组
    }

    // 更新数据的函数
    function updateData($pdo, $id, $newRoomId, $newRoomName) {
        $sql = "UPDATE roomData SET room_id = :newRoomId, room_name = :newRoomName WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['newRoomId' => $newRoomId, 'newRoomName' => $newRoomName, 'id' => $id]);
        return ["message" => "Room updated successfully."];
    }

    // 删除数据的函数
    function deleteData($pdo, $id) {
        $sql = "DELETE FROM roomData WHERE room_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return ["message" => "Room deleted successfully."];
    }

    // 修改列名的函数
    function renameColumn($pdo, $oldColumnName, $newColumnName) {
        $sql = "ALTER TABLE roomData RENAME COLUMN :oldColumnName TO :newColumnName";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':oldColumnName', $oldColumnName, PDO::PARAM_STR);
        $stmt->bindParam(':newColumnName', $newColumnName, PDO::PARAM_STR);
        $stmt->execute();
        return ["message" => "Column '$oldColumnName' renamed to '$newColumnName' successfully."];
    }

    // 获取列名的函数
    function getColumnNames($pdo, $tableName) {
        $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = :tableName";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['tableName' => $tableName]);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $columns; // 返回列名数组
    }

    // 删除列的函数
    function dropColumn($pdo, $columnName) {
        $sql = "ALTER TABLE roomData DROP COLUMN :columnName";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':columnName', $columnName, PDO::PARAM_STR);
        try {
            $stmt->execute();
            return ["message" => "Column '$columnName' deleted successfully."];
        } catch (PDOException $e) {
            return [message => "Column deletion failed: ". $e->getMessage()];
        }
    }


    // 获取所有表的函数
    function getAllTables($pdo) {
        $sql = "SELECT tablename FROM pg_tables WHERE schemaname = 'public'";
        $stmt = $pdo->query($sql);
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $tables;
    }

    // 处理请求
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $action = $_POST['action'];

        $response = []; // 创建响应数组

        if ($action === 'create_table') {
            $response = createTable($pdo);
        } elseif ($action === 'drop_table') {
            $response = dropTable($pdo);
        } elseif ($action === 'add') {
            $room_id = $_POST['room_id'];
            $room_name = $_POST['room_name'];
            $response = addData($pdo, $room_id, $room_name);
        } elseif ($action === 'get') {
            // $response = json_decode(getData($pdo), true);
            $response = getData($pdo);
        } elseif ($action === 'update') {
            $id = $_POST['id'];
            $newRoomId = $_POST['new_room_id'];
            $newRoomName = $_POST['new_room_name'];
            $response = updateData($pdo, $id, $newRoomId, $newRoomName);
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            $response = deleteData($pdo, $id);
        } elseif ($action === 'rename_column') {
            $oldColumnName = $_POST['old_column_name'];
            $newColumnName = $_POST['new_column_name'];
            $response = renameColumn($pdo, $oldColumnName, $newColumnName);
        } elseif ($action === 'get_columns') {
            $tableName = $_POST['table_name'];
            $response = getColumnNames($pdo, $tableName);
        } elseif ($action === 'drop_column') {
            $columnName = $_POST['column_name'];
            $response = dropColumn($pdo, $columnName);
        } elseif ($action === 'get_all_tables') {  // 新增操作
            $response = getAllTables($pdo);
        }

        echo json_encode($response); // 返回 JSON 格式响应
    }
    ob_end_flush(); // 输出缓冲内容
?>