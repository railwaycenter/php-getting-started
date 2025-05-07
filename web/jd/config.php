<?php
    /**
     * Created by PhpStorm.
     * User:
     * Date: 2020/7/22
     * Time: 10:56
     */
    header("Content-Type: text/html;charset=utf-8");
    date_default_timezone_set('Asia/Shanghai');//'Asia/Shanghai'   亚洲/上海
    require  'Medoo.php';
    use Medoo\Medoo;

    $database = new Medoo([
        // required
        'database_type' => 'mysql',
        'database_name' => getenv('database_name'),
        'server' => getenv('database_server'),
        'username' => getenv('database_username'),
        'password' => getenv('database_password'),

//        // [optional]
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'port' => getenv('database_port'),
//
//        // [optional] Table prefix
//        'prefix' => 'PREFIX_',
//
//        // [optional] Enable logging (Logging is disabled by default for better performance)
//        'logging' => true,
//
//        // [optional] MySQL socket (shouldn't be used with server and port)
//        'socket' => '/tmp/mysql.sock',
//
//        // [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
//        'option' => [
//            PDO::ATTR_CASE => PDO::CASE_NATURAL
//        ],
//
//        // [optional] Medoo will execute those commands after connected to the database for initialization
//        'command' => [
//            'SET SQL_MODE=ANSI_QUOTES'
//        ]
    ]);

    //print_r($datas);

    function xsleep($second)
    {
        usleep($second*1000000);
    }
?>