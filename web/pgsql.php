
<?php
    header('Content-Type: application/json'); // 设置返回类型为 JSON
    $host = 'ep-rapid-disk-71674411.us-east-1.pg.koyeb.app';
    $port = '5432';
    $dbname = 'koyebdb';
    $user = 'koyeb-adm';
    $password = '5pgHstORKSD1';

    try {
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
        exit;
    }

    // 创建表的函数
    function createTable($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS roomData (
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
        $sql = "INSERT INTO roomData (room_id, room_name) VALUES (:room_id, :room_name)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['room_id' => $room_id, 'room_name' => $room_name]);
        return ["message" => "Room added successfully."];
    }

    // 获取数据的函数
    function getData($pdo) {
        $sql = "SELECT * FROM roomData";
        $stmt = $pdo->query($sql);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($rooms);
    }

    // 更新数据的函数
    function updateData($pdo, $id, $newRoomId, $newRoomName) {
        $sql = "UPDATE roomData SET room_id = :newRoomId, room_name = :newRoomName WHERE room_id = :id";
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
        $sql = "ALTER TABLE roomData RENAME COLUMN $oldColumnName TO $newColumnName";
        $pdo->exec($sql);
        return ["message" => "Column '$oldColumnName' renamed to '$newColumnName' successfully."];
    }

    // 获取列名的函数
    function getColumnNames($pdo) {
        $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = 'roomData'";
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return json_encode($columns);
    }

    // 删除列的函数
    function dropColumn($pdo, $columnName) {
        $sql = "ALTER TABLE roomData DROP COLUMN $columnName";
        $pdo->exec($sql);
        return ["message" => "Column '$columnName' deleted successfully."];
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
            $response = json_decode(getData($pdo), true);
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
            $response = json_decode(getColumnNames($pdo), true);
        } elseif ($action === 'drop_column') {
            $columnName = $_POST['column_name'];
            $response = dropColumn($pdo, $columnName);
        }

        echo json_encode($response); // 返回 JSON 格式响应
    }

?>
