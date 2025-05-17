<?php
// 数据库配置文件，分离配置以便于维护和部署
// 版本号 v1.0.0
return [
    'database_type' => 'mysql',
    'database_name' => getenv('database_name_ch'), // 替换为你的数据库名
    'server' => getenv('database_server'),
    'username' => getenv('database_username'),
    'password' => getenv('database_password'),

    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_general_ci',
    'port' => getenv('database_port'),
    'error' => PDO::ERRMODE_EXCEPTION
];
?>