<?php
// 数据库配置文件，分离配置以便于维护和部署
// 版本号 v1.0.22

// 本地开发环境下加载 .env 文件（如果存在）
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name               = trim($name);
            $value              = trim($value);
            putenv("$name=$value");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
}

return [
    'database_type' => 'mysql',
    'database_name' => getenv('database_name_ch'), // 替换为你的数据库名
    'server'        => getenv('database_server'),
    'username'      => getenv('database_username'),
    'password'      => getenv('database_password'),
    'port'          => getenv('database_port') ?: 3306,
    'charset'       => 'utf8mb4',
    'collation'     => 'utf8mb4_general_ci',
    'error'         => PDO::ERRMODE_EXCEPTION
];
?>