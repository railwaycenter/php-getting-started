
<?php
    $host = 'ep-rapid-disk-71674411.us-east-1.pg.koyeb.app';
    $port = '5432';
    $dbname = 'koyebdb';
    $user = 'koyeb-adm';
    $password = '5pgHstORKSD1';

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "Connected to the database successfully!";
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }


    // SQL语句，用于创建表
    // $sql = "
    // CREATE TABLE IF NOT EXISTS roomData (
    //     id SERIAL PRIMARY KEY,
    //     room_id VARCHAR(255) NOT NULL,
    //     room_name VARCHAR(255),
    //     add_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    // );
    // ";
    //
    // // 执行SQL语句
    // $pdo->exec($sql);


    // try {
    //     $sql = "INSERT INTO roomData (room_id, room_name) VALUES (:value1, :value2)";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute(['value1' => '145263', 'value2' => '沐苏【国一镜】']);
    //     echo "Data inserted successfully!";
    // } catch (PDOException $e) {
    //     echo 'Insert failed: ' . $e->getMessage();
    // }

    // 增加数据的函数（Create）
    function addData($pdo, $room_id, $room_name) {
        $sql = "INSERT INTO roomData (room_id, room_name) VALUES (:room_id, :room_name)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':room_id' => $room_id, ':room_name' => $room_name]);
        return "Data added successfully!";
    }

    // 读取数据的函数（Read）
    function getData($pdo) {
        $sql = "SELECT * FROM roomData";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = "<h3>Room List:</h3>";
        foreach ($results as $row) {
            $output .= "Room ID: " . htmlspecialchars($row['room_id']) . " - Room Name: " . htmlspecialchars($row['room_name']) . "<br>";
        }
        return $output;
    }

    // 更新数据的函数（Update）
    function updateData($pdo, $id, $newRoomId, $newRoomName) {
        $sql = "UPDATE roomData SET room_id = :room_id, room_name = :room_name WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':room_id' => $newRoomId, ':room_name' => $newRoomName, ':id' => $id]);
        return "Data updated successfully!";
    }

    // 删除数据的函数（Delete）
    function deleteData($pdo, $id) {
        $sql = "DELETE FROM roomData WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return "Data deleted successfully!";
    }

    // 处理请求
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $action = $_POST['action'];

        if ($action === 'add') {
            $room_id = $_POST['room_id'];
            $room_name = $_POST['room_name'];
            echo addData($pdo, $room_id, $room_name);
        } elseif ($action === 'get') {
            echo getData($pdo);
        } elseif ($action === 'update') {
            $id = $_POST['id'];
            $newRoomId = $_POST['new_room_id'];
            $newRoomName = $_POST['new_room_name'];
            echo updateData($pdo, $id, $newRoomId, $newRoomName);
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            echo deleteData($pdo, $id);
        }
    }

?>
