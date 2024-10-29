
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
        echo 'Connection failed: ' . $e->getMessage();
    }


    // SQL语句，用于创建表
    // $sql = "
    // CREATE TABLE IF NOT EXISTS roomData (
    //     id SERIAL PRIMARY KEY,
    //     roomId VARCHAR(255) NOT NULL,
    //     roomName VARCHAR(255),
    //     addDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    // );
    // ";
    //
    // // 执行SQL语句
    // $pdo->exec($sql);


    // try {
    //     $sql = "INSERT INTO roomData (roomId, roomName) VALUES (:value1, :value2)";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute(['value1' => '145263', 'value2' => '沐苏【国一镜】']);
    //     echo "Data inserted successfully!";
    // } catch (PDOException $e) {
    //     echo 'Insert failed: ' . $e->getMessage();
    // }


    try {
        $sql = "SELECT * FROM roomData";
        $stmt = $pdo->query($sql);

        if ($stmt) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                echo "Table is empty or data could not be fetched.";
            } else {
                foreach ($results as $row) {
                    print_r($row); // 输出数据
                }
            }
        } else {
            echo "Query failed.";
        }
    } catch (PDOException $e) {
        echo 'Data fetch failed: ' . $e->getMessage();
    }

?>
